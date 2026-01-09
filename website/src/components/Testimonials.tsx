import { Card, CardContent } from "@/components/ui/card"

const testimonials = [
  {
    quote:
      "Blitz Cache reduced our page load time from 3.2s to 0.15s. The difference is night and day!",
    author: "Sarah Johnson",
    role: "WordPress Developer",
    avatar: "SJ",
  },
  {
    quote:
      "We've tried many caching plugins, but Blitz Cache is the easiest to set up and most effective. Zero configuration needed!",
    author: "Michael Chen",
    role: "Agency Owner",
    avatar: "MC",
  },
  {
    quote:
      "Our e-commerce site saw a 94% reduction in server load after installing Blitz Cache. Incredible performance!",
    author: "Emma Rodriguez",
    role: "WooCommerce Store Owner",
    avatar: "ER",
  },
  {
    quote:
      "The Cloudflare integration is seamless. Cache purges happen automatically. It's like magic!",
    author: "David Park",
    role: "DevOps Engineer",
    avatar: "DP",
  },
  {
    quote:
      "Finally, a caching plugin that just works. No complicated settings, no conflicts. Highly recommended!",
    author: "Lisa Thompson",
    role: "Freelance Developer",
    avatar: "LT",
  },
  {
    quote:
      "Our mobile page scores went from 45 to 98 after using Blitz Cache. The speed improvement is remarkable.",
    author: "James Wilson",
    role: "Marketing Manager",
    avatar: "JW",
  },
]

export function Testimonials() {
  return (
    <section id="testimonials" className="py-20 md:py-32 bg-muted/30">
      <div className="container mx-auto max-w-6xl px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Loved by <span className="gradient-text">Developers</span> Worldwide
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Join thousands of satisfied users who have transformed their WordPress performance with Blitz Cache.
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {testimonials.map((testimonial, index) => (
            <Card key={index} className="hover:shadow-lg transition-shadow duration-300">
              <CardContent className="pt-6">
                <div className="mb-4">
                  <div className="text-4xl text-purple-600 mb-2">"</div>
                  <p className="text-sm leading-relaxed">{testimonial.quote}</p>
                </div>
                <div className="flex items-center gap-3 mt-4 pt-4 border-t">
                  <div className="h-10 w-10 rounded-full bg-gradient-to-r from-purple-600 to-blue-600 flex items-center justify-center text-white font-semibold text-sm">
                    {testimonial.avatar}
                  </div>
                  <div>
                    <div className="font-semibold text-sm">{testimonial.author}</div>
                    <div className="text-xs text-muted-foreground">{testimonial.role}</div>
                  </div>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">10K+</div>
            <div className="text-sm text-muted-foreground">Active Users</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">4.9/5</div>
            <div className="text-sm text-muted-foreground">User Rating</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">50K+</div>
            <div className="text-sm text-muted-foreground">Downloads</div>
          </div>
          <div>
            <div className="text-3xl md:text-4xl font-bold gradient-text mb-2">1M+</div>
            <div className="text-sm text-muted-foreground">Pages Cached</div>
          </div>
        </div>
      </div>
    </section>
  )
}
