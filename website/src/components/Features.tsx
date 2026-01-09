import {
  Zap,
  Cloud,
  Shield,
  Settings,
  Gauge,
  Layers,
  Database,
  Globe,
  RefreshCw,
  Minimize2
} from "lucide-react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

const features = [
  {
    icon: Zap,
    title: "10x Faster Page Loads",
    description: "Serve cached HTML instead of generating dynamic pages. See instant improvements in page load times.",
    gradient: "from-purple-500 to-blue-500",
  },
  {
    icon: Cloud,
    title: "Cloudflare Integration",
    description: "Automatic Cloudflare cache purge and optional Edge caching for global performance optimization.",
    gradient: "from-blue-500 to-cyan-500",
  },
  {
    icon: Database,
    title: "File-Based Caching",
    description: "Zero database overhead with intelligent file-based caching using MD5 hash keys for maximum performance.",
    gradient: "from-green-500 to-emerald-500",
  },
  {
    icon: Minimize2,
    title: "GZIP Compression",
    description: "Reduce bandwidth by up to 80% with pre-compressed files. Smaller files = faster loading.",
    gradient: "from-orange-500 to-red-500",
  },
  {
    icon: Layers,
    title: "HTML Minification",
    description: "Automatically minify cached HTML to reduce file size without breaking your site's functionality.",
    gradient: "from-pink-500 to-rose-500",
  },
  {
    icon: RefreshCw,
    title: "Smart Cache Purge",
    description: "Automatically purge related pages when content changes. Keep your cache fresh without manual work.",
    gradient: "from-indigo-500 to-purple-500",
  },
  {
    icon: Gauge,
    title: "Cache Preloading",
    description: "Automatically warm up cache after purging. No cold cache delays for your visitors.",
    gradient: "from-cyan-500 to-blue-500",
  },
  {
    icon: Shield,
    title: "WooCommerce Ready",
    description: "Smart handling of cart, checkout, and product pages. E-commerce optimized out of the box.",
    gradient: "from-violet-500 to-purple-500",
  },
  {
    icon: Settings,
    title: "Zero Configuration",
    description: "Works out of the box with smart defaults. No complicated settings to configure.",
    gradient: "from-amber-500 to-orange-500",
  },
  {
    icon: Globe,
    title: "Browser Cache Headers",
    description: "Optimized cache headers for static assets. Improve return visitor experience with proper caching.",
    gradient: "from-teal-500 to-cyan-500",
  },
]

export function Features() {
  return (
    <section id="features" className="py-20 md:py-32 bg-muted/30">
      <div className="container mx-auto max-w-6xl px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Everything You Need for <span className="gradient-text">Lightning Speed</span>
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Blitz Cache combines the best caching strategies with modern web technologies
            to deliver unparalleled WordPress performance.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {features.map((feature, index) => (
            <Card key={index} className="group hover:shadow-lg transition-all duration-300 border-2 hover:border-purple-500/20">
              <CardHeader>
                <div className={`inline-flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-r ${feature.gradient} text-white mb-4 group-hover:scale-110 transition-transform duration-300`}>
                  <feature.icon className="h-6 w-6" />
                </div>
                <CardTitle className="text-xl">{feature.title}</CardTitle>
              </CardHeader>
              <CardContent>
                <CardDescription className="text-base">
                  {feature.description}
                </CardDescription>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">10x</div>
            <div className="text-sm text-muted-foreground">Faster Load Times</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">80%</div>
            <div className="text-sm text-muted-foreground">Bandwidth Saved</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">98%+</div>
            <div className="text-sm text-muted-foreground">Cache Hit Ratio</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">0</div>
            <div className="text-sm text-muted-foreground">Configuration Needed</div>
          </div>
        </div>
      </div>
    </section>
  )
}
