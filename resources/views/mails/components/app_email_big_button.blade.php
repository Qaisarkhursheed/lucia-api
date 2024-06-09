<x-app-email-paragraph>
    <table
        border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:separate;width:100%;line-height:100%;"
    >
        <tr>
            <td
                align="center" bgcolor="{{ $bgcolor }}" role="presentation"
                style="border:none;border-radius:3px;cursor:auto;mso-padding-alt:10px 25px;background:{{ $bgcolor }};"
                valign="middle"
            >
                <a href="{{ $link }}"
                   style="cursor: pointer; width: 100%;display: block;">
                <p
                    style="display:inline-block;background:{{ $bgcolor }};color:#ffffff;font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:16px;font-weight:normal;line-height:120%;margin:0;text-decoration:none;text-transform:none;padding:10px 25px;mso-padding-alt:0px;border-radius:3px;"
                >
                    {{ $slot }}
                </p>
                </a>
            </td>
        </tr>
    </table>
</x-app-email-paragraph>

