<?php

use App\ModelsExtended\BookingOcr;

/* @var BookingOcr $bookingOcr */

?>
@extends( 'mails.app_email_base' )

@section( 'body-content' )
    <x-app-email-paragraph>
        Hi {{ $bookingOcr->user->first_name }},
    </x-app-email-paragraph>

    @if( $bookingOcr->ocr_status_id === \App\ModelsExtended\OcrStatus::IMPORTED )

        <x-app-email-paragraph>
            We are glad to inform you that the <a href="{{ $bookingOcr->document_url }}"> file ( {{ $bookingOcr->file_name }} ) </a> you uploaded for
            your itinerary ( {{ optional($bookingOcr->itinerary)->title }} )
            <strong style="font-weight: bold; color: green; text-decoration: underline; text-transform: uppercase">has been successfully imported.</strong>
            <br/>
        </x-app-email-paragraph>

        <x-app-email-big-button bgcolor="#BA886E" link="{{ uiAppUrl( 'itinerary-details/' . $bookingOcr->itinerary_id ) }}">
            Check itinerary
        </x-app-email-big-button>

    @elseif( $bookingOcr->ocr_status_id === \App\ModelsExtended\OcrStatus::FAILED_IMPORTATION || $bookingOcr->ocr_status_id === \App\ModelsExtended\OcrStatus::FAILED_RECOGNITION )
        <x-app-email-paragraph>
            We are sorry to inform you that the <a href="{{ $bookingOcr->document_url }}"> file ( {{ $bookingOcr->file_name }} ) </a> you uploaded for
            your itinerary ( {{ optional($bookingOcr->itinerary)->title }} ) was
            <strong style="font-weight: bold; color: red; text-decoration: underline; text-transform: uppercase">not processable</strong>
            by our OCR engine at the moment.<br/>
        </x-app-email-paragraph>
        <x-app-email-paragraph>
            Our technical team has been informed about the incident.<br/>
        </x-app-email-paragraph>
    @endif

@endsection
