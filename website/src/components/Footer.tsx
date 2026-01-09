import { Github, Twitter, Mail, Bolt } from "lucide-react"

export function Footer() {
  const currentYear = new Date().getFullYear()

  const footerLinks = {
    product: [
      { label: "Features", href: "#features" },
      { label: "Why Free?", href: "#pricing" },
      { label: "Testimonials", href: "#testimonials" },
      { label: "FAQ", href: "#faq" },
    ],
    resources: [
      { label: "Documentation", href: "https://github.com/BlitzCache/blitzcache/tree/main/docs" },
      { label: "Installation Guide", href: "https://github.com/BlitzCache/blitzcache/blob/main/docs/installation.md" },
      { label: "Configuration", href: "https://github.com/BlitzCache/blitzcache/blob/main/docs/configuration.md" },
      { label: "Hooks Reference", href: "https://github.com/BlitzCache/blitzcache/blob/main/docs/HOOKS.md" },
    ],
    community: [
      { label: "GitHub", href: "https://github.com/BlitzCache/blitzcache" },
      { label: "Issues", href: "https://github.com/BlitzCache/blitzcache/issues" },
      { label: "Contributing", href: "https://github.com/BlitzCache/blitzcache/blob/main/CONTRIBUTING.md" },
      { label: "Security", href: "https://github.com/BlitzCache/blitzcache/blob/main/SECURITY.md" },
    ],
    company: [
      { label: "About", href: "https://github.com/ersinkoc" },
      { label: "Blog", href: "https://blitzcache.com/blog" },
      { label: "Contact", href: "mailto:hello@blitzcache.com" },
      { label: "Sponsor", href: "https://github.com/sponsors/ersinkoc" },
    ],
  }

  const socialLinks = [
    { icon: Github, href: "https://github.com/BlitzCache/blitzcache", label: "GitHub" },
    { icon: Twitter, href: "https://twitter.com/ersinkoc", label: "Twitter" },
    { icon: Mail, href: "mailto:hello@blitzcache.com", label: "Email" },
  ]

  return (
    <footer className="bg-muted/50 border-t">
      <div className="container mx-auto max-w-6xl px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-8">
          <div className="lg:col-span-2">
            <a href="/" className="flex items-center space-x-3 mb-4 group">
              <div className="h-10 w-10 rounded-xl bg-gradient-to-r from-emerald-500 to-cyan-500 flex items-center justify-center group-hover:scale-105 transition-transform">
                <Bolt className="h-6 w-6 text-white" />
              </div>
              <span className="font-black text-xl">
                <span className="text-foreground">Blitz</span>
                <span className="bg-gradient-to-r from-emerald-500 to-cyan-500 bg-clip-text text-transparent"> Cache</span>
              </span>
            </a>
            <p className="text-sm text-muted-foreground mb-4 max-w-sm">
              Lightning-fast WordPress caching with Cloudflare integration.
              Zero configuration, maximum performance.
            </p>
            <div className="flex gap-4">
              {socialLinks.map((social, index) => (
                <a
                  key={index}
                  href={social.href}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="text-muted-foreground hover:text-emerald-600 transition-colors"
                  aria-label={social.label}
                >
                  <social.icon className="h-5 w-5" />
                </a>
              ))}
            </div>
          </div>

          <div>
            <h3 className="font-semibold mb-4">Product</h3>
            <ul className="space-y-2">
              {footerLinks.product.map((link, index) => (
                <li key={index}>
                  <a
                    href={link.href}
                    className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-semibold mb-4">Resources</h3>
            <ul className="space-y-2">
              {footerLinks.resources.map((link, index) => (
                <li key={index}>
                  <a
                    href={link.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-semibold mb-4">Community</h3>
            <ul className="space-y-2">
              {footerLinks.community.map((link, index) => (
                <li key={index}>
                  <a
                    href={link.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-semibold mb-4">Company</h3>
            <ul className="space-y-2">
              {footerLinks.company.map((link, index) => (
                <li key={index}>
                  <a
                    href={link.href}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
                  >
                    {link.label}
                  </a>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <div className="mt-12 pt-8 border-t">
          <div className="flex flex-col md:flex-row justify-between items-center gap-4">
            <p className="text-sm text-muted-foreground">
              © {currentYear} Blitz Cache. All rights reserved. Made with ❤️ by{" "}
              <a
                href="https://github.com/ersinkoc"
                target="_blank"
                rel="noopener noreferrer"
                className="text-emerald-600 hover:underline"
              >
                Ersin KOÇ
              </a>
            </p>
            <div className="flex gap-6">
              <a
                href="https://github.com/BlitzCache/blitzcache/blob/main/LICENSE"
                target="_blank"
                rel="noopener noreferrer"
                className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
              >
                License
              </a>
              <a
                href="https://github.com/BlitzCache/blitzcache/blob/main/SECURITY.md"
                target="_blank"
                rel="noopener noreferrer"
                className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
              >
                Security
              </a>
              <a
                href="https://github.com/BlitzCache/blitzcache/blob/main/docs/configuration.md"
                target="_blank"
                rel="noopener noreferrer"
                className="text-sm text-muted-foreground hover:text-emerald-600 transition-colors"
              >
                Privacy
              </a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  )
}
