<x-mail::message>
# Hello, {{ $user->name }}! 👋

We are glad u are here, your application for finance managment is ready to help you take control of your money.

<x-mail::button :url="route('dashboard')">
Go to Dashboard
</x-mail::button>

If you have any questions, feel free to answer to this mail.

With respect,<br>
{{ config('app.name') }}
</x-mail::message>