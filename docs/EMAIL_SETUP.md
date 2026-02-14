# Email Service Setup Guide

Production email configuration for Arma Battles.

## Current Status

**.env setting:** `MAIL_MAILER=log`

This means emails are currently written to `storage/logs/laravel.log` instead of being sent. This works for development but **must be changed for production**.

## Production Email Services

Choose one of these services for production email delivery:

### Option 1: Mailgun (Recommended - Free tier available)

**Pros:** 5,000 free emails/month, simple setup, Laravel native support

**Setup:**

1. **Create account:** https://www.mailgun.com/
2. **Add & verify domain:** Add armabattles.com and verify DNS records
3. **Get credentials:** Copy API key and domain from Mailgun dashboard
4. **Update .env:**
   ```bash
   MAIL_MAILER=mailgun
   MAIL_FROM_ADDRESS="noreply@armabattles.com"
   MAIL_FROM_NAME="Arma Battles"

   MAILGUN_DOMAIN=mg.armabattles.com  # Or armabattles.com
   MAILGUN_SECRET=your-mailgun-api-key-here
   MAILGUN_ENDPOINT=api.mailgun.net
   ```

5. **Install package:** (already included in Laravel)
   ```bash
   composer require symfony/mailgun-mailer symfony/http-client
   ```

**DNS Records Required:**
```
TXT  @ "v=spf1 include:mailgun.org ~all"
TXT  mailo._domainkey  "k=rsa; p=YOUR_PUBLIC_KEY"
CNAME mg.armabattles.com mailgun.org
```

---

### Option 2: SendGrid (Transactional email specialist)

**Pros:** 100 free emails/day, good deliverability, detailed analytics

**Setup:**

1. **Create account:** https://sendgrid.com/
2. **Create API key:** Settings → API Keys → Create API Key (Full Access)
3. **Update .env:**
   ```bash
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.sendgrid.net
   MAIL_PORT=587
   MAIL_USERNAME=apikey
   MAIL_PASSWORD=your-sendgrid-api-key-here
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="noreply@armabattles.com"
   MAIL_FROM_NAME="Arma Battles"
   ```

4. **Verify sender:** Add noreply@armabattles.com as verified sender

---

### Option 3: AWS SES (Cheapest at scale)

**Pros:** $0.10 per 1,000 emails, unlimited sending (after verification)

**Setup:**

1. **AWS Console:** Navigate to Amazon SES
2. **Verify domain:** Add armabattles.com and verify DNS
3. **Request production access:** (Starts in sandbox mode)
4. **Create SMTP credentials:** SMTP Settings → Create SMTP Credentials
5. **Update .env:**
   ```bash
   MAIL_MAILER=ses
   MAIL_FROM_ADDRESS="noreply@armabattles.com"
   MAIL_FROM_NAME="Arma Battles"

   AWS_ACCESS_KEY_ID=your-access-key-id
   AWS_SECRET_ACCESS_KEY=your-secret-access-key
   AWS_DEFAULT_REGION=eu-west-1  # Or your region
   ```

6. **Install AWS SDK:**
   ```bash
   composer require aws/aws-sdk-php
   ```

---

### Option 4: Standard SMTP (Your own mail server or hosting provider)

**Pros:** Full control, works with any SMTP server

**Setup:**

Update .env:
```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@armabattles.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@armabattles.com"
MAIL_FROM_NAME="Arma Battles"
```

---

## Email Functionality in Arma Battles

Once email is configured, these features will work:

### Currently Implemented:
- Password reset emails
- Team invitation emails
- Match reminder emails (24h and 1h before matches)
- Achievement unlock notifications
- Tournament registration confirmations
- Ban appeal status updates

### Email Verification (Optional):
- Can be enabled for email/password registrations
- Steam/Google login users bypass verification
- See `docs/EMAIL_VERIFICATION.md`

---

## Testing Email Setup

### Test with Artisan Tinker:

```bash
php artisan tinker
```

```php
Mail::raw('Test email from Arma Battles', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

If successful, you'll receive the test email. If it fails, check:
1. .env settings are correct
2. API credentials are valid
3. Domain is verified (for Mailgun/SES)
4. `php artisan config:clear` after .env changes

---

## Troubleshooting

### Emails not sending:
```bash
# Clear config cache
php artisan config:clear

# Check queue (emails are queued by default)
php artisan queue:work

# Check logs
tail -f storage/logs/laravel.log
```

### "Connection could not be established" error:
- Check MAIL_PORT (587 for TLS, 465 for SSL)
- Verify MAIL_ENCRYPTION matches port (tls or ssl)
- Check firewall allows outbound SMTP

### Emails going to spam:
- Add SPF, DKIM, and DMARC DNS records
- Use verified sender domain
- Avoid spammy content in emails
- Use transactional email service (not personal Gmail)

---

## Monitoring & Limits

**Mailgun Free Tier:**
- 5,000 emails/month
- After limit: $0.80 per 1,000 emails

**SendGrid Free Tier:**
- 100 emails/day (3,000/month)
- After limit: Starts at $19.95/month

**AWS SES:**
- 62,000 free emails/month (if hosted on AWS)
- Otherwise: $0.10 per 1,000 emails

**Monitor usage:**
- Check service dashboards regularly
- Set up billing alerts
- Arma Battles sends ~100-500 emails/day at medium activity

---

## Production Checklist

Before launch:

- [ ] Choose email service provider
- [ ] Create account and get API credentials
- [ ] Verify domain (add DNS records)
- [ ] Update .env with credentials
- [ ] Clear Laravel config cache: `php artisan config:clear`
- [ ] Send test email via tinker
- [ ] Ensure queue worker is running: `php artisan queue:work`
- [ ] Monitor first few days for deliverability issues
- [ ] Set up billing alerts

---

## Need Help?

- Mailgun docs: https://documentation.mailgun.com/
- SendGrid docs: https://docs.sendgrid.com/
- AWS SES docs: https://docs.aws.amazon.com/ses/
- Laravel Mail docs: https://laravel.com/docs/12.x/mail

If you need custom email template design, contact the development team.
