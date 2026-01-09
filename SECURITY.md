# Security Policy

## Supported Versions

We actively support the following versions of Blitz Cache with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x  | ✅                |
| < 1.0  | ❌                |

## Reporting a Vulnerability

We take the security of Blitz Cache seriously. If you believe you have found a security vulnerability, please follow these steps to report it:

### How to Report

**DO NOT** report security vulnerabilities through public GitHub issues. Instead:

1. **Email**: Send details to [security@blitzcache.com](mailto:security@blitzcache.com)
2. **PGP Key**: Use our PGP key for encrypted communication
   - Key ID: `0x1234567890ABCDEF`
   - Fingerprint: `XXXX XXXX XXXX XXXX XXXX XXXX XXXX XXXX XXXX XXXX`

### What to Include

Please include the following information in your report:

1. **Description**: A clear description of the vulnerability
2. **Impact**: What could an attacker do with this vulnerability?
3. **Reproduction**: Steps to reproduce the vulnerability
4. **Environment**:
   - WordPress version
   - PHP version
   - Blitz Cache version
   - Theme and active plugins
5. **Proof of Concept**: Code or screenshots demonstrating the vulnerability

### What We Promise

We are committed to working with security researchers to:

- ✅ Acknowledge receipt of your report within 48 hours
- ✅ Provide an initial assessment within 7 days
- ✅ Keep you informed of our progress
- ✅ Credit you in our security acknowledgments (if desired)
- ✅ Not take legal action against researchers who follow this policy

### What We Ask

We ask that researchers:

- ✅ Report vulnerabilities responsibly
- ✅ Give us reasonable time to fix issues before disclosure
- ✅ Do not access user data beyond what's necessary to demonstrate the vulnerability
- ✅ Do not perform testing on production sites
- ✅ Do not publicly disclose the vulnerability until we've had a chance to fix it

## Security Measures

Blitz Cache implements the following security measures:

### Input Sanitization
- All user inputs are sanitized using WordPress sanitization functions
- URLs are validated and escaped
- Form data is properly validated

### Data Encryption
- API tokens are encrypted using AES-256-CBC encryption
- Sensitive data is never stored in plain text
- Passwords and secrets use secure hashing

### Access Control
- Admin functionality requires `manage_options` capability
- AJAX actions verify nonces for CSRF protection
- User permissions are properly checked

### Cache Security
- Cache files are stored outside the web root
- `.htaccess` rules prevent direct access to cache files
- Cache directory has `index.php` to prevent directory listing
- Cache files have unique MD5 hash names

### SQL Injection Prevention
- No custom SQL queries (WordPress handles all DB operations)
- When needed, `$wpdb->prepare()` is used for queries

### XSS Prevention
- All output is properly escaped
- User-generated content is sanitized
- WordPress escaping functions are used throughout

### CSRF Protection
- Nonces are used for all state-changing operations
- AJAX requests verify nonces
- Forms include nonce fields

## Security Best Practices

For users of Blitz Cache:

1. **Keep WordPress Updated**: Always run the latest version
2. **Keep PHP Updated**: Use supported PHP versions (8.0+)
3. **Strong Passwords**: Use strong passwords for admin accounts
4. **Limited Access**: Only grant necessary permissions to users
5. **Regular Backups**: Maintain regular backups of your site
6. **Monitor Logs**: Check WordPress debug logs regularly
7. **Use HTTPS**: Always use SSL/TLS certificates
8. **Regular Updates**: Keep all plugins and themes updated

## Security Acknowledgments

We would like to thank the following security researchers who have responsibly disclosed vulnerabilities:

- [Researcher Name](link) - Vulnerability description - Date
- [Researcher Name](link) - Vulnerability description - Date

## Bug Bounty

Currently, we do not offer a monetary bug bounty program. However, we:

- ✅ Publicly acknowledge security researchers (with permission)
- ✅ Provide contributor recognition
- ✅ Offer early access to new versions
- ✅ Provide security researcher badges

## Disclosure Policy

Our vulnerability disclosure policy follows a **90-day timeline**:

1. **Day 0**: Vulnerability reported
2. **Day 1-7**: Initial assessment and reproduction
3. **Day 8-60**: Development and testing of fix
4. **Day 61-90**: Coordinated disclosure with vendor
5. **Day 90+**: Public disclosure if vendor hasn't fixed

## Contact Information

For security-related questions:

- **Email**: [security@blitzcache.com](mailto:security@blitzcache.com)
- **GitHub Security**: Use GitHub's private vulnerability reporting feature

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [WordPress Security](https://developer.wordpress.org/themes/theme-security/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)

---

**Thank you for helping keep Blitz Cache and the WordPress community safe!**
