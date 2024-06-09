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
        <mj-text mj-class="heading">You have a new message from {{ $advisorChat->sender->first_name }}</mj-text>
        <mj-text>Hey {{ $advisorChat->receiver->first_name }}, you have a mew message in  #{{ $advisorChat->id }} from {{ $advisorChat->sender->first_name }}:</mj-text>
                <mj-divider border-width="2px" border-color="white" />
        <mj-text font-size="18px" color="#000" align="center" font-family="Montserrat, monospace">
            {{ $advisorChat->plain_text }}
        </mj-text>
                <mj-text font-size="16px" color="#BA886E" font-weight="700" align="center" font-family="Montserrat, monospace">{{ $advisorChat->sender->first_name }}</mj-text>
                <mj-divider border-width="2px" border-color="white" />
                <mj-button width="100%"  href="{{ copilotAppUrl(  ) }}">VIEW FULL MESSAGE IN LUCIA</mj-button>
        <mj-text>The Lucia Team</mj-text>
      </mj-column>
    </mj-section>
     <mj-section background-color="#fffff">
   <mj-column>
     <mj-text align="center" font-size="10px" line-height="16px" color="#000000">©Lucia 2022. All rights reserved.</mj-text>
   </mj-column>
 </mj-section>
  </mj-body>
</mjml>
