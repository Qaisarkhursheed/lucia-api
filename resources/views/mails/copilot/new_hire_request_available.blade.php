<?php

/* @var \App\ModelsExtended\AdvisorRequest $advisorRequest */
/* @var \App\ModelsExtended\User $user */

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <title>
        Lucia
    </title>
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--<![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        #outlook a { padding:0; }
        body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
        table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
        img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
        p { display:block;margin:13px 0; }
    </style>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:AllowPNG/>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <!--[if lte mso 11]>
    <style type="text/css">
        .mj-outlook-group-fix { width:100% !important; }
    </style>
    <![endif]-->

    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant:wght@300;400&family=Montserrat:wght@100;400;700&display=swap" rel="stylesheet" type="text/css">
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css2?family=Cormorant:wght@300;400&family=Montserrat:wght@100;400;700&display=swap);
    </style>
    <!--<![endif]-->



    <style type="text/css">
        @media only screen and (min-width:480px) {
            .mj-column-per-100 { width:100% !important; max-width: 100%; }
        }
    </style>
    <style media="screen and (min-width:480px)">
        .moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }
    </style>


    <style type="text/css">



        @media only screen and (max-width:480px) {
            table.mj-full-width-mobile { width: 100% !important; }
            td.mj-full-width-mobile { width: auto !important; }
        }

    </style>
    <style type="text/css">

    </style>

</head>
<body style="word-spacing:normal;background-color:#ffffff;">


<div style="background-color:#ffffff;">


    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->


    <div style="margin:0px auto;max-width:600px;">

        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->

                    <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                        <tbody>
                                        <tr>
                                            <td style="width:70px;">

                                                <img height="auto" src="{{myAssetUrl('logo-lucia-full-letters.png')}}" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="70">

                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </div>

                    <!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
            </tbody>
        </table>

    </div>


    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#ffffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->


    <div style="background:#ffffff;background-color:#ffffff;margin:0px auto;max-width:600px;">

        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ffffff;background-color:#ffffff;width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->

                    <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Cormorant, Arial;font-size:34px;font-weight:100;letter-spacing:0;line-height:38px;text-align:center;color:#000000;">New request from {{ $advisorRequest->user->first_name }}</div>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, Arial;font-size:14px;line-height:160%;text-align:center;color:#000000;">Hey {{ $user->first_name }},</div>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, Arial;font-size:14px;line-height:160%;text-align:center;color:#000000;">You have a new request from {{ $advisorRequest->user->first_name }}:</div>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, monospace;font-size:16px;font-weight:700;line-height:160%;text-align:center;color:#BA886E;">{{ $advisorRequest->request_title }}</div>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, monospace;font-size:18px;line-height:160%;text-align:center;color:#000000;">{{ $advisorRequest->notes }}</div>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <p style="border-top:solid 2px white;font-size:1px;margin:0px auto;width:100%;">
                                    </p>

                                    <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 2px white;font-size:1px;margin:0px auto;width:550px;" role="presentation" width="550px" ><tr><td style="height:0;line-height:0;"> &nbsp;
                              </td></tr></table><![endif]-->


                                </td>
                            </tr>

                            <tr>
                                <td align="center" vertical-align="middle" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <a href="{{ copilotAppUrl(  ) }}" style="text-decoration: none; cursor: pointer">
                                    <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:100%;line-height:100%;">
                                        <tbody>
                                        <tr>
                                            <td align="center" bgcolor="#BA886E" role="presentation" style="border:none;border-radius:50px 50px 50px 50px;cursor:auto;mso-padding-alt:20px 20px;background:#BA886E;" valign="middle">
                                                <p style="display:inline-block;background:#BA886E;color:#ffffff;font-family:Montserrat, Arial;font-size:14px;font-weight:700;line-height:120%;letter-spacing:2;margin:0;text-decoration:none;text-transform:none;padding:20px 20px;mso-padding-alt:0px;border-radius:50px 50px 50px 50px;">
                                                    ACCEPT REQUEST
                                                </p>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    </a>

                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, Arial;font-size:14px;line-height:160%;text-align:center;color:#000000;">The Lucia Team</div>

                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </div>

                    <!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
            </tbody>
        </table>

    </div>


    <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#fffff" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->


    <div style="background:#fffff;background-color:#fffff;margin:0px auto;max-width:600px;">

        <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#fffff;background-color:#fffff;width:100%;">
            <tbody>
            <tr>
                <td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
                    <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->

                    <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">

                        <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                            <tbody>

                            <tr>
                                <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">

                                    <div style="font-family:Montserrat, Arial;font-size:10px;line-height:16px;text-align:center;color:#000000;">©Lucia 2022. All rights reserved.</div>

                                </td>
                            </tr>

                            </tbody>
                        </table>

                    </div>

                    <!--[if mso | IE]></td></tr></table><![endif]-->
                </td>
            </tr>
            </tbody>
        </table>

    </div>


    <!--[if mso | IE]></td></tr></table><![endif]-->


</div>

</body>
</html>
