@component('mail::message')



{{-- Greeting --}}
<h2 style="text-align:center; color:#333;">Hello!</h2>

<p style="text-align:center; font-size:16px; color:#555;">
    Your verification code is:
</p>

{{-- Code Box --}}
<div style="text-align:center; margin:20px 0;">
    <span style="
        display:inline-block;
        background:#f1f1f1;
        border:1px dashed #ccc;
        padding:15px 25px;
        font-size:24px;
        font-weight:bold;
        letter-spacing:2px;
        border-radius:8px;
        color:#111;
    ">
        {{ $code }}
    </span>
</div>

<p style="text-align:center; font-size:14px; color:#777;">
    This code will expire in <strong>{{ config('secure-otp.expiry_minutes', 5) }} minutes</strong>.
</p>

{{-- Button --}}
<div style="text-align:center; margin:30px 0;">
    @component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
        Go to App
    @endcomponent
</div>

<p style="text-align:center; font-size:14px; color:#999;">
    Thanks,<br>
    {{ config('app.name') }}
</p>
@endcomponent
