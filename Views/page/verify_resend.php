<div class="container py-3" style="margin-top: 56px;">
    <p>A verification email has been sent to the address you registered. Please check your inbox (and possibly your spam folder) and follow the instructions in the email to verify your account.</p>
    <form action="/form/verify/resend" method="post">
        <input type="hidden" name="csrf_token" value="<?= Helpers\CrossSiteForgeryProtection::getToken(); ?>">
        <button type="submit" class="btn btn-primary">Resend</button>
    </form>
</div>
