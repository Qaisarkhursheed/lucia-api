<mjml>
  <mj-head>
    <mj-title>Lucia</mj-title>
    <mj-font name="Montserrat" href="https://fonts.googleapis.com/css2?family=Cormorant:wght@300;400&family=Montserrat:wght@100;400;700&display=swap" />
    <mj-style inline="inline"> .anchor { color:#FF2F9A; text-decoration:none; } .anchor-footer {color: #ffffff;} table .is-centered { text-align: center; } table .is-left { text-align: left; } table .is-right { text-align:right; } table tr th { background-color: #fafa; text-align: left; } .box-shadow { box-shadow: 10px 5px 5px #f2f2f2; } .link-nostyle { color: #BA886E; text-decoration: none } </mj-style>
    <mj-attributes>
      <mj-class name="heading" align="center" line-height="38px" font-size="34px" letter-spacing="0" font-weight="100" font-family="Cormorant, Arial" />      <mj-button background-color="#BA886E" font-size="14px" align="center" letter-spacing="2" font-weight="700" font-family="Montserrat, Arial" inner-padding="20px 20px" />
      <mj-table color="#000" cellpadding="6px" font-size="14px" align="center" font-family="Montserrat, Arial" />
      <mj-text color="#000" font-size="14px" align="center" line-height="160%" font-family="Montserrat, Arial" />
    </mj-attributes>
  </mj-head>
  <mj-body background-color="#fff">
    <mj-section>
  <mj-column>
    <mj-image src="{{myAssetUrl('logo-lucia-full-letters.png')}}" align="center" width="70px">
    </mj-image>
  </mj-column>
</mj-section>
    <mj-section background-color="#fff">
      <mj-column>
        <mj-text mj-class="heading">Hi, welcome to lucia!</mj-text>
        <mj-text>You have been invited to check this itinerary. Please, click the button below to view the itinerary.</mj-text>
        <mj-divider border-width="2px" border-color="white" />
        <mj-image src="{{$itinerary_logo_url}}" align="center" width="540px" height="350px" border-radius="250px 250px 0px 0px"></mj-image>

       <mj-button width="100%"  href="{{ uiAppUrl( 'public/itinerary/' ) . $share_itinerary_key }}"> VIEW ITINERARY</mj-button>
      </mj-column>
    </mj-section>
    <mj-section>

      </mj-column>
    </mj-section>
     <mj-section background-color="#fffff">
   <mj-column>
     <mj-text align="center" font-size="10px" line-height="16px" color="#000000">©Lucia 2022. All rights reserved.</mj-text>
   </mj-column>
 </mj-section>
  </mj-body>
</mjml>
