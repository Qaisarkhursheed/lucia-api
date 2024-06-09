<?php

namespace App\Http\Controllers\Admin;

use App\ModelsExtended\User;
use Illuminate\Support\Carbon;
use App\Http\Responses\OkResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ItineraryWeeklyCount;
use App\Models\ItineraryMonthlyCount;
use App\ModelsExtended\AdvisorRequest;
use App\Repositories\Stripe\StripeReportSDK;
use Illuminate\Database\Eloquent\Collection;
use App\ModelsExtended\StripeSubscriptionHistory;
use App\ModelsExtended\Role;

class DashboardController extends Controller
{
    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function fetch()
    {
        return new OkResponse([
            "users_count" => User::Agents()->count(),
            "top_stats" => $this->getSubscriptionStats(),
            "account_balance" => $this->getStripeOwnerAccountBalance(),
            "itinerary_chart" => [
                "monthly" => $this->getItineraryMonthly(),
                "weekly" => $this->getItineraryWeekly(),
            ],
            "latest_subscriptions" => $this->getLatestSubscriptions(),
            'agent'=>[
                "LoginActivityMonthly"=>$this->getAgentMonthlyLoginActivity(),
                "LoginActivityWeekly"=>$this->getAgentWeeklyLoginActivity(),
                
            ],
            "SubscriptionMonthWise"=>$this->SubscriptionMonthWise(),
            "CustomerStatistic"=>$this->getCustomerRelatedStatistic()
        ]);
    }

    /**
     * @return array
     */
    private function getSubscriptionStats(): array
    {
        $history = StripeSubscriptionHistory::query()
            ->whereNotNull( "stripe_subscription" )
            ->where( "stripe_subscription->status", 'active'  )
            ->get();


        $subscription_count = $history->groupBy( function ( StripeSubscriptionHistory $item ){
                return $item->user_id;
            } )->count();

        $subscriptions = $history
            ->groupBy( function ( StripeSubscriptionHistory $item ){
                    return $item->stripe_subscription['id'];
                } )
            ->map(
                function ( Collection $collection ) {
                return array_to_object( $collection->first()->stripe_subscription );
            });


        $revenue = $subscriptions->sum( function ( $sub ){
            return $sub->plan->amount/100;
        } );

        $averagePrice = $subscriptions->average( function ( $sub ){
            return $sub->plan->amount/100;
        } );

        return [
            "subscriptions" => $subscription_count,
            "revenue" => $revenue,
            "average_price" => $averagePrice,
        ];
    }

    /**
     * @return array
     */
    private function getLatestSubscriptions(): array
    {
        $history = StripeSubscriptionHistory::with("user")
            ->limit(10)
            ->orderByDesc("created_at")
            ->get();

       return $history->map( function ( StripeSubscriptionHistory $item ){
                return [
                    "created_at" => $item->created_at,
                    "billing_name" => $item->user->name,
                    "interval" => $item->plan_interval,
                    "total" => $item->amount_decimal,
                    "status" => $item->status ,
                    "current_period_start" => $item->current_period_start,
                    "current_period_end" => $item->current_period_end,
                ];
            })
           ->toArray();
    }

    /**
     * @return array
     * @throws \Stripe\Exception\ApiErrorException
     */
    private function getStripeOwnerAccountBalance(): array
    {
        $sdk = new StripeReportSDK();
        $balance = (object) $sdk->retrieveOwnersBalance()->toArray();
        return [
                "available" => $balance->available[0]["amount"]/100,
                "pending" => $balance->pending[0]["amount"]/100,
            ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection
     */
    private function getItineraryMonthly()
    {
        return ItineraryMonthlyCount::query()
            ->orderByDesc("year_created")
            ->orderByDesc("month_created")
            ->limit("12")
            ->get()
            ->reverse()
            ->values();
    }

    private function getItineraryWeekly()
    {
        return ItineraryWeeklyCount::query()
            ->orderByDesc("year_created")
            ->orderByDesc("week_created")
            ->limit("12")
            ->get()
            ->reverse()
            ->values();
    }

    private function getAgentMonthlyLoginActivity(){

        $SQL = "SELECT 
        LPAD(month(us.created_at), 2, 0) as month,year(us.created_at) as month_year,count(us.id) as records 
        FROM `user_session` us INNER JOIN users on users.id = us.user_id 
        where role_id = ".Role::Agent." 
        group by month_year,month 
        order by month desc limit 12;"; 
        $tot_records = array();
        $results = DB::select($SQL);
        $tot_records['ThisMonth'] = isset($results[0])?$results[0]->records:0;;
        $tot_records['LastMonth'] = isset($results[1])?$results[1]->records:0;;

        $difference = $tot_records['ThisMonth'] - $tot_records['LastMonth'];
        $percentage = 0;
        $increase_decrease = '';
        if($difference > 0){
            $percentage = round(($difference/$tot_records['ThisMonth'])*100,2);
            $increase_decrease = 'increase';
        }else{
            $difference = $tot_records['LastMonth'] - $tot_records['ThisMonth'];
            $percentage = round(($difference/$tot_records['LastMonth'])*100,2);
            $increase_decrease = 'decrease';
        }

        $tot_records['percentage'] = $percentage;
        $tot_records['increase_decrease'] = $increase_decrease;



        $tot_records['MonthlyMAU'] = $results;
        return $tot_records;

    }

    private function getAgentWeeklyLoginActivity(){

        $SQL = "SELECT
        EXTRACT(week from us.created_at) AS Week,EXTRACT(year from us.created_at) AS year,
        COUNT(us.id) AS records
        FROM user_session us INNER JOIN users on users.id = us.user_id
        WHERE role_id = ".Role::Agent."
        GROUP BY EXTRACT(week from us.created_at),EXTRACT(year from us.created_at)
        ORDER BY Week desc limit 12";
        $tot_records = array();
        $results = DB::select($SQL);
        $tot_records['ThisWeek'] = isset($results[0])?$results[0]->records:0;
        $tot_records['LastWeek'] = isset($results[1])?$results[1]->records:0;

        $difference = $tot_records['ThisWeek'] - $tot_records['LastWeek'];
        $percentage = 0;
        $increase_decrease = '';
        if($difference > 0){
            $percentage = round(($difference/$tot_records['ThisWeek'])*100,2);
            $increase_decrease = 'increase';
        }else{
            $difference = $tot_records['LastWeek'] - $tot_records['ThisWeek'];
            $percentage = round(($difference/$tot_records['LastWeek'])*100,2);
            $increase_decrease = 'decrease';
        }

        $tot_records['percentage'] = $percentage;
        $tot_records['increase_decrease'] = $increase_decrease;

        $WeeklyRecords = array();

        foreach($results as $k=> $WeeklyResult){
            $week_start = new \DateTime();
            $week_start->setISODate($WeeklyResult->year,$WeeklyResult->Week);
            $WeeklyRecords[] = array('week'=>$WeeklyResult->Week,'records'=>$WeeklyResult->records,'date'=>$week_start->format('Y-m-d'));
        }
        
        $tot_records['WeeklyMAU'] = $WeeklyRecords;
        return  $tot_records;

    }

    private function SubscriptionMonthWise(){

        $sdk = new StripeReportSDK();
        $invoicesObject = (object) $sdk->retrieveAllInvoice()->toArray();
        $inovice_array = array();
        $dataArray = array();
        $Statuses = array();
        $i = 0;
        $Month = '';
        $Months = array();
        foreach($invoicesObject->data as $invoice){
            
              
          //  if($invoice['billing_reason'] == 'subscription_create'):

                 $i = ($Month !=date('M', $invoice['created']))?$i=1:$i;
                 $Month = date('M', $invoice['created']);
                if(!in_array($Month,$Months)){
                 $Months[] = $Month;
                }
                $Statuses[] = $invoice['status'];
                $inovice_array[$invoice['status']][$Month] =     $i;
                // $inovice_array[$invoice['status']][$Month]['amount_remaining'] =$invoice['amount_remaining'];
                // $inovice_array[$invoice['status']][$Month]['amount_due'] =      $invoice['amount_due'];
                // $inovice_array[$invoice['status']][$Month]['created'] = date('m/d/Y', $invoice['created']);
                // $inovice_array[$invoice['status']][$Month]['month'] = date('M', $invoice['created']);
                // $inovice_array[$invoice['status']][$Month]['customer_name'] =$invoice['customer_name'];
                // $inovice_array[$invoice['status']][$Month]['due_date'] =$invoice['due_date'];
                // $inovice_array[$invoice['status']][$Month]['description'] =$invoice->lines->data[0]['description'] ?? '';
                // $inovice_array[$invoice['status']][$Month]['description'] =$invoice->lines->data[0]->plan['interval'] ?? '';
                $i++;
               
           // endif;

        }
       
       
       
        $findArray['Months'] = array_unique($Months,SORT_REGULAR);
      
        $dataSets = array();
        $j = 0;
        foreach(array_unique($Statuses) as $k=> $Status){
            $valuesArray = array();
            $dataSets[$j]['label'] = $Status;
            foreach($inovice_array[$Status] as $val){
                $valuesArray[] = $val;
            }
            $dataSets[$j]['data'] = $valuesArray;
            $dataSets[$j]['borderWidth'] = 3;
            $dataSets[$j]['axis'] = 'y';
            $dataSets[$j]['fill'] = false;
            $dataSets[$j]['backgroundColor'] = [$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor()];
              
            $dataSets[$j]['borderColor'] = [$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor(),$this->getColor()];
            $dataSets[$j]['color'] = '#36A2EB';
            $j++;
        }

        $findArray['labels'] = array_unique($Months);
        $findArray['dataSets'] = $dataSets;
        


        return $findArray;
        
    }

    private function getCustomerRelatedStatistic(){

        $tot_records = array();

        //todo: change to proper laravel query builder
        $SQL = "SELECT MONTHNAME(created_at) as month,count(*) as total from stripe_subscription_history where status='active' AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND (MONTH(created_at) = MONTH(CURRENT_DATE())) group by month  limit 1;  ";
        $totalActiveThisMonth = DB::select($SQL);
        //todo: change to proper laravel query builder
        $SQL = "SELECT MONTHNAME(created_at) as month,count(*) as total from stripe_subscription_history where status='active' AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND (MONTH(created_at) = MONTH(CURRENT_DATE())-1) group by month  limit 1;  ";
        $totalActivePrevMonth = DB::select($SQL);
        //todo: change to proper laravel query builder
        $SQL = "SELECT count(*) as total from stripe_subscription_history where status='cancelled' AND user_id in(SELECT user_id from stripe_subscription_history where status='active' AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND (MONTH(created_at) = MONTH(CURRENT_DATE())-1) ) AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND (MONTH(created_at) = MONTH(CURRENT_DATE())); ";
        $CancelledThisMonth = DB::select($SQL);
        //todo: change to proper laravel query builder
        //$SQL = "SELECT year(created_at) as month_year,LPAD(month(created_at), 2, 0) as month,count(*) as total from stripe_subscription_history where status='active' AND YEAR(created_at) = YEAR(CURRENT_DATE()) group by month,month_year limit 12;";
       // $ChurnReport = DB::select($SQL);


        $ChurnReport = DB::table('stripe_subscription_history')
                    ->select(DB::raw('year(created_at) as month_year'),DB::raw('LPAD(month(created_at), 2, 0) as month'),DB::raw('count(*) as total'))
                    ->whereRaw('status="active" AND YEAR(created_at) = YEAR(CURRENT_DATE())')
                    ->groupBy('month','month_year')
                    ->limit(12)
                    ->get();
                    //SELECT sum(arp.amount) as amount,week(arp.created_at) as week FROM advisor_request_payment arp where arp.stripe_charge_id is not null and arp.stripe_refund_id is null group by week(arp.created_at) order by week(arp.created_at) desc; 
        $WeeklyAverage = DB::table('advisor_request_payment')
                    ->select( DB::raw('sum(advisor_request_payment.amount) as amount'),DB::raw('week(advisor_request_payment.created_at) as week'))
                    ->whereNull('advisor_request_payment.stripe_refund_id')
                    ->whereNotNull('advisor_request_payment.stripe_charge_id')
                    ->groupByRaw('week(advisor_request_payment.created_at)')
                    ->limit(2)
                    ->orderBy('week', 'desc')
                    ->get();

        $MonthlyAverage = DB::table('advisor_request_payment')
                    ->select( DB::raw('sum(advisor_request_payment.amount) as amount'),DB::raw('month(advisor_request_payment.created_at) as month'))
                    ->whereNull('advisor_request_payment.stripe_refund_id')
                    ->whereNotNull('advisor_request_payment.stripe_charge_id')
                    ->groupByRaw('month(advisor_request_payment.created_at)')
                    ->orderBy('month', 'desc')
                    ->limit(2)
                    ->get();

        
        $CustomerWisePayment = DB::table('advisor_request')
                    ->select('advisor_request.created_by_id', DB::raw('SUM(advisor_request_payment.amount) as total_req_amount'),'users.name')
                    ->join('advisor_request_payment', 'advisor_request.id', '=', 'advisor_request_payment.advisor_request_id')
                    ->leftjoin('users', 'users.id', '=', 'advisor_request.created_by_id')
                    ->whereNull('advisor_request_payment.stripe_refund_id')
                    ->whereNotNull('advisor_request_payment.stripe_charge_id')
                    ->groupBy('advisor_request.created_by_id')
                    ->orderBy('total_req_amount', 'desc')
                    ->get();

        $totalAmount = $CustomerWisePayment->sum('total_req_amount');
        $totalCustomerWhoPaid = $CustomerWisePayment->count('created_by_id');
        //Weekly Calculation Start
        $tot_records['ThisWeek'] = isset($WeeklyAverage[0])?round($WeeklyAverage[0]->amount/$totalCustomerWhoPaid,2):0;
        $tot_records['LastWeek'] = isset($WeeklyAverage[1])?round($WeeklyAverage[1]->amount,2):0;
            
        $difference = $tot_records['ThisWeek'] - $tot_records['LastWeek'];
        $percentage = 0;
        $increase_decrease = '';
        if($difference > 0){
            $percentage = round(($difference/$tot_records['ThisWeek'])*100,2);
            $increase_decrease = 'increase';
        }else{
            $difference = $tot_records['LastWeek'] - $tot_records['ThisWeek'];
            $percentage = round(($difference/$tot_records['LastWeek'])*100,2);
            $increase_decrease = 'decrease';
        }
        $tot_records['week_percentage'] = $percentage;
        $tot_records['week_increase_decrease'] = $increase_decrease;
        //Weekly Calculation End

        //Monthly Calculation Start
        $tot_records['ThisMonth'] = isset($MonthlyAverage[0])?round($MonthlyAverage[0]->amount/$totalCustomerWhoPaid,2):0;
        $tot_records['LastMonth'] = isset($MonthlyAverage[1])?round($MonthlyAverage[1]->amount,2):0;
            
        $difference = $tot_records['ThisMonth'] - $tot_records['LastMonth'];
        $percentage = 0;
        $increase_decrease = '';
        if($difference > 0){
            $percentage = round(($difference/$tot_records['ThisMonth'])*100,2);
            $increase_decrease = 'increase';
        }else{
            $difference = $tot_records['LastMonth'] - $tot_records['ThisMonth'];
            $percentage = round(($difference/$tot_records['LastMonth'])*100,2);
            $increase_decrease = 'decrease';
        }
        $tot_records['month_percentage'] = $percentage;
        $tot_records['month_increase_decrease'] = $increase_decrease;
        //Monthly Calculation End

        $avgPerCustomerSpent = ($totalCustomerWhoPaid>0)?round($totalAmount/$totalCustomerWhoPaid,2):0;

        $tot_records['totalActiveThisMonth'] = $totalActiveThisMonth['total'] ?? 0;
        $tot_records['totalActivePrevMonth'] = $totalActivePrevMonth['total'] ?? 0;
        $tot_records['CancelledThisMonth'] = $CancelledThisMonth['total'] ?? 0;
        $tot_records['ChurnReport'] = $ChurnReport;
        $tot_records['totalAmount'] = $totalAmount;
        $tot_records['TopCustomerPaymentWise'] = $CustomerWisePayment;
        $tot_records['avgPerCustomerSpent'] = $avgPerCustomerSpent;
        
        $difference = $tot_records['totalActiveThisMonth'] - $tot_records['totalActivePrevMonth'];
        $percentage = 0;
        $increase_decrease = '';
        if($difference > 0){
            $percentage = ($tot_records['totalActiveThisMonth'] > 0)?round(($difference/$tot_records['totalActiveThisMonth'])*100,2):0;
            $increase_decrease = 'increase';
        }else{
            $difference = $tot_records['totalActivePrevMonth'] - $tot_records['totalActiveThisMonth'];
            $percentage = ($tot_records['totalActivePrevMonth'] > 0)?round(($difference/$tot_records['totalActivePrevMonth'])*100,2):0;
            $increase_decrease = 'decrease';
        }

        $tot_records['percentage'] = $percentage;
        $tot_records['increase_decrease'] = $increase_decrease;
        return $tot_records;

    }

    public function getColor(){
        $r = 255;
        $g = round(99*rand(0,2),2);
        $b = round(310*rand(0,2),2);
        return 'rgb('.$r.','.$g.','.$b.')';
    }

      
}
