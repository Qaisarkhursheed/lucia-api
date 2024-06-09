<?php

namespace App\Http\Controllers\Admin\Agents;

use Illuminate\Support\Arr;
use App\ModelsExtended\Role;
use App\ModelsExtended\User;
use Illuminate\Http\Request;
use App\ModelsExtended\UserRole;
use App\Http\Responses\OkResponse;
use App\Http\Controllers\Controller;
use App\ModelsExtended\PreferredPartners;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use App\ModelsExtended\ApplicationProductPrice;
use App\Repositories\Stripe\StripeSubscriptionSDK;
use App\Http\Controllers\Enhancers\CRUDEnabledController;
use App\Http\Controllers\Enhancers\PaginableTraitController;
use App\Http\Controllers\Enhancers\YajraPaginableTraitController;

class PreferredPartnersController extends CRUDEnabledController implements \App\Http\Controllers\Enhancers\IYajraEloquentResultProcessorInterface
{
    use PaginableTraitController, YajraPaginableTraitController;

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|null|User
     */
    private $user;
    protected int $TARGETED_ROLE = Role::Agent;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->orderByColumnName = 'company_name';
    }

    public function fetchAll()
    {
        $this->fetchAll();
    }

    /**
     * @return OkResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function basicPagination()
    {
        return new OkResponse($this->paginate($this->filterQuery($this->getQuery())));
    }

      /**
     * Rules to validate
     * @return array
     */
    public function getCommonRules()
    {
        return [
            'company_name' => 'required|string|max:100',
            'contact_person_name' => 'required|string|max:100',
            'monthly_price' => 'required|string|max:100',
            'annual_price' => 'required|string|max:100',
            'contact_email' => 'required|email|max:200',
            'website' => 'filled|url|max:300',
            'logo' => 'nullable|string|max:100',
        ];
    }

    public function updatePartner(Request $request)
    {
        
        $model = PreferredPartners::find($request->input('id'));
        $logoURL = '';
        if($request->file('logo')):
            $logoURL =  PreferredPartners::savePartnerLogo($request);
        endif;
        
        $request->merge(['logo' => $logoURL,]);
        $model->update( $this->validatedRules($this->getCommonRules()) );
        return $this->fetch();
    }

    /**
     * @inheritDoc
     */
    public function store(Request $request)
    {
        $logoURL = '';
        if($request->file('logo')):
            $logoURL =  PreferredPartners::savePartnerLogo($request);
        endif;
        $request->merge(['logo' => $logoURL]);
        $this->model = PreferredPartners::create( $this->validatedRules( $this->getCommonRules() ) );
        return $this->fetch();
    }

     /**
     * @inheritDoc
     */
    public function assignpartner(Request $request)
    {
        $user_id = $request->input('selected_agent_id');
        $preferred_partner_id = $request->input('preferred_partner_id');
        $User = User::find($user_id);
        $old_partner_id = $User->preferred_partner_id;
        $User->preferred_partner_id = $preferred_partner_id;
        $message = "System error, please try again";
        if($User->save()){
            $message = 'Partner '.$User->partner->company_name.' bas been succesfully assigned to user ['.$User->name.'].';
            $userRole = UserRole::getUserRole( $this->TARGETED_ROLE, $User->id);
            $subscriptionArray =  $userRole->getStripeSubscription();
            //To get user old subscription plan monthly or yearly
            $plan = isset($subscriptionArray['items']['data']['plan']['interval'])?$subscriptionArray['items']['data']['plan']['interval']:'monthly';
            $price_id = ($plan == 'monthly')?$User->partner->monthly_price:$User->partner->annual_price;

            if(!empty($price_id)){
                $this->updateUserSubscription($userRole,$price_id);
            }else{
                $message." but subscription still pending.";
            }
            
        }
        return new OkResponse(['message'=>$message]);
    }

    /**
     * @inheritDoc
     */
    public function removepartner(Request $request)
    {
        $message = "System error, please try again";
        $user_id = $request->input('user_id');
        $User = User::find($user_id);
        $User->preferred_partner_id = NULL;
        if($User->save()){
            $message = 'Account removed from partner account to normal.';
            $userRole = UserRole::getUserRole( $this->TARGETED_ROLE, $User->id);
            $subscriptionArray =  $userRole->getStripeSubscription();
            //To get user old subscription plan monthly or yearly
            $price_id = isset($subscriptionArray['items']['data']['plan']['id'])?$subscriptionArray['items']['data']['plan']['id']:'';
    
            if(!empty($price_id)){
                $message .=' '.$this->updateUserSubscription($userRole,$price_id);
            }else{
                $message.=" But subscription still pending.";
            }
        }
        return new OkResponse(['message'=>$message]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function gridPagination()
    {
        return $this->paginateYajra($this);
    }

    /**
     * @return Builder
     */
    protected function getQuery(): Builder
    {
        return PreferredPartners::query();
    }

     /**
     * @inheritDoc
     */
    public function getDataQuery(): Builder
    {
        return $this->getQuery();
    }

    /**
     * @param Builder $query
     * @return Builder|mixed
     */
    protected function filterQuery(Builder $query)
    {
        return $query->when($this->search, function (Builder $builder) {
            $search = $this->search;
            $builder->where( function (Builder $builder) use ( $search ) {
                $builder->where("company_name", 'like', "%$search%")
                    ->orWhere("contact_email", 'like', "%$search%")
                    ->orWhere("contact_person_name", 'like', "%$search%");
            });
        });
    }

    /**
     * @param User[]|Collection $result
     * @return array
     */
    public function processYajraEloquentResult($result): array
    {
        return $result->map(function (PreferredPartners $partner) {
            return [
                'id' => $partner->id,
                'company_name' => $partner->company_name,
                'contact_email' => $partner->contact_email,
                'contact_person_name' => $partner->contact_person_name,
                'monthly_price' => $partner->monthly_price,
                'annual_price' => $partner->annual_price,
                'website' => $partner->website,
                'logo' => $partner->logo,
                'created_at' => $partner->created_at,
            ];
        })->toArray();
    }

    public function updateUserSubscription(UserRole $userRole,$stripe_price_id){
        
        $SDK = new StripeSubscriptionSDK();
        if (!$userRole->has_valid_license && !$userRole->user->stripe_subscription_histories()->exists()) {
          
            if($userRole->user->user_stripe_account->getStripeCustomerIdAttribute()){
                $SDK->updateSubscription($userRole->user->user_stripe_account->getStripeCustomerIdAttribute(),['price' => $stripe_price_id ]);

            }
        }
    }
}
