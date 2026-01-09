"use client"

import { Check, Zap } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"

const plans = [
  {
    name: "Free",
    price: "Free",
    description: "Perfect for personal blogs and small websites",
    features: [
      "File-based caching",
      "GZIP compression",
      "HTML minification",
      "Browser cache headers",
      "Basic cache purge",
      "WooCommerce support",
      "Community support",
    ],
    cta: "Download Free",
    popular: false,
    href: "https://github.com/ersinkoc/blitz-cache/releases",
  },
  {
    name: "Pro",
    price: "Free",
    description: "For businesses and high-traffic sites",
    features: [
      "Everything in Free",
      "Cloudflare integration",
      "Smart cache purge",
      "Cache preloading",
      "Advanced exclusions",
      "EDD & LearnDash support",
      "Email support",
      "Performance monitoring",
    ],
    cta: "Download Pro",
    popular: true,
    href: "https://github.com/ersinkoc/blitz-cache/releases",
  },
]

export function Pricing() {
  return (
    <section id="pricing" className="py-20 md:py-32">
      <div className="container mx-auto max-w-6xl px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Simple, <span className="gradient-text">Transparent Pricing</span>
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Blitz Cache is free and open source. Choose the version that fits your needs.
          </p>
        </div>

        <div className="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
          {plans.map((plan, index) => (
            <Card key={index} className={`relative ${plan.popular ? 'border-purple-500 shadow-lg scale-105' : ''}`}>
              {plan.popular && (
                <Badge className="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-purple-600 to-blue-600">
                  Most Popular
                </Badge>
              )}
              <CardHeader className="text-center pb-8">
                <CardTitle className="text-2xl mb-2">{plan.name}</CardTitle>
                <div className="text-4xl font-bold mb-4">
                  {plan.price}
                  {plan.price !== "Free" && <span className="text-lg text-muted-foreground font-normal">/month</span>}
                </div>
                <CardDescription className="text-base">{plan.description}</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4">
                <ul className="space-y-3">
                  {plan.features.map((feature, i) => (
                    <li key={i} className="flex items-start gap-3">
                      <Check className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                      <span className="text-sm">{feature}</span>
                    </li>
                  ))}
                </ul>
              </CardContent>
              <CardFooter>
                <Button
                  className={`w-full ${plan.popular ? 'bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700' : ''}`}
                  variant={plan.popular ? "default" : "outline"}
                  asChild
                >
                  <a href={plan.href} target="_blank" rel="noopener noreferrer">
                    {plan.cta}
                  </a>
                </Button>
              </CardFooter>
            </Card>
          ))}
        </div>

        <div className="mt-16 text-center">
          <Card className="max-w-3xl mx-auto border-2 border-dashed border-purple-500/20">
            <CardContent className="pt-8">
              <div className="flex items-center justify-center gap-2 mb-4">
                <Zap className="h-5 w-5 text-purple-600" />
                <span className="font-semibold">Both versions are 100% free and open source!</span>
              </div>
              <p className="text-muted-foreground mb-4">
                No hidden fees, no subscriptions, no limits. Just pure performance.
              </p>
              <p className="text-sm text-muted-foreground">
                Support development on{" "}
                <a
                  href="https://github.com/sponsors/ersinkoc"
                  className="text-purple-600 hover:underline"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  GitHub Sponsors
                </a>
              </p>
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  )
}
