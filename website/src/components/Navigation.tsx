import { useState } from "react"
import { Menu, X, Bolt } from "lucide-react"
import { Button } from "@/components/ui/button"
import { ThemeToggle } from "@/components/ThemeToggle"

export function Navigation() {
  const [isOpen, setIsOpen] = useState(false)

  const navItems = [
    { href: "#features", label: "Features" },
    { href: "#pricing", label: "Why Free?" },
    { href: "#testimonials", label: "Testimonials" },
    { href: "#faq", label: "FAQ" },
  ]

  return (
    <header className="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
      <div className="container flex h-20 items-center justify-center">
        <div className="flex items-center space-x-8">
          <a href="/" className="flex items-center space-x-3 group">
            <div className="h-10 w-10 rounded-xl bg-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
              <Bolt className="h-6 w-6 text-white" />
            </div>
            <div className="flex flex-col">
              <span className="font-black text-xl leading-tight">
                <span className="text-foreground">Blitz</span>
                <span className="text-emerald-600"> Cache</span>
              </span>
            </div>
          </a>

          <nav className="hidden md:flex items-center space-x-1 border-l border-border pl-8">
            {navItems.map((item) => (
              <a
                key={item.href}
                href={item.href}
                className="px-4 py-2 text-sm font-medium transition-colors hover:text-emerald-600 hover:bg-emerald-500/10 rounded-lg"
              >
                {item.label}
              </a>
            ))}
          </nav>
        </div>

        <div className="hidden md:flex items-center space-x-3 absolute right-0 pr-4">
          <ThemeToggle />
          <Button variant="ghost" size="sm" asChild>
            <a href="https://github.com/ersinkoc/blitz-cache/tree/main/docs">Docs</a>
          </Button>
          <Button size="sm" className="bg-emerald-600 hover:bg-emerald-700 text-white border-0" asChild>
            <a href="https://github.com/ersinkoc/blitz-cache/releases">
              <Bolt className="h-4 w-4 mr-2" />
              Get Started
            </a>
          </Button>
        </div>

        <div className="flex items-center gap-2 md:hidden absolute right-0 pr-4">
          <ThemeToggle />
          <button
            onClick={() => setIsOpen(!isOpen)}
            aria-label="Toggle menu"
            className="p-2 hover:bg-muted rounded-lg transition-colors"
          >
            {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>
      </div>

      {isOpen && (
        <div className="md:hidden border-t border-border">
          <div className="container py-4 space-y-3">
            <div className="flex items-center justify-center space-x-3 pb-4 border-b border-border">
              <a href="/" className="flex items-center space-x-2 group" onClick={() => setIsOpen(false)}>
                <div className="h-8 w-8 rounded-lg bg-emerald-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                  <Bolt className="h-5 w-5 text-white" />
                </div>
                <span className="font-black text-lg leading-tight">
                  <span className="text-foreground">Blitz</span>
                  <span className="text-emerald-600"> Cache</span>
                </span>
              </a>
            </div>
            {navItems.map((item) => (
              <a
                key={item.href}
                href={item.href}
                className="block px-4 py-3 text-sm font-medium hover:bg-emerald-500/10 hover:text-emerald-600 rounded-lg transition-colors text-center"
                onClick={() => setIsOpen(false)}
              >
                {item.label}
              </a>
            ))}
            <div className="pt-4 space-y-2">
              <Button variant="outline" className="w-full" asChild>
                <a href="https://github.com/ersinkoc/blitz-cache/tree/main/docs">Documentation</a>
              </Button>
              <Button className="w-full bg-emerald-600 hover:bg-emerald-700 text-white border-0" asChild>
                <a href="https://github.com/ersinkoc/blitz-cache/releases">Get Started</a>
              </Button>
            </div>
          </div>
        </div>
      )}
    </header>
  )
}
