<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertCategoriesIntoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `categories` (`id`, `name`, `category_type`, `created_at`, `updated_at`) VALUES(NULL, 'Dining', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Activity', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Dining - VIP', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Rail', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Transfer', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Ferry', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Cruise', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Car Rental', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'DMC', 'TYPE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Axus', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Travefy', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'SION', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'TravelJoy', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'AgentMate', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Revelex', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Saber', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'UMapped', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'GDS', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Sequence', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'ClientBase Online', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'CBO', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Tres', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Canary', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'TikTok', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Instagram', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Canva', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'SquareSpace', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Adobe', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'InDesign', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Photoshop', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'FinalCut Pro', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'iMovie', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Travel Contact', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'MailChimp', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'FloDesk', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'SalesForce', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Zoho', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Google Flights', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Survey Monkey', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'HootSuite', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Unsplashed', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'HubSpot', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'TripCreator', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Excel', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Google Sheets', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Google Docs', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Facebook', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Amadeus', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Galileo', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Worldspan', 'TECHNOLOGY', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Proposal', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Booking', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Quoting', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Supplier Relations', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Commission Chasing', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Itinerary building', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Admin', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Invoicing', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Content Creation', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Copywriting', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Social Media', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Marketing', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Points', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Miles', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Destination Research', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Research', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Follow Up', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
        (NULL, 'Confirming Res', 'SKILLS', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("delete from `categories`");
    }
}