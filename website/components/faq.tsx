"use client"

import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion"

const faqs = [
  {
    question: "What is Blitz Cache?",
    answer:
      "Blitz Cache is a high-performance WordPress caching plugin that uses file-based caching to dramatically improve your site's speed. It requires zero configuration and works out of the box with smart defaults.",
  },
  {
    question: "How does Blitz Cache improve performance?",
    answer:
      "Blitz Cache creates static HTML files of your pages and serves them instead of generating pages dynamically. This reduces server load, database queries, and PHP execution time, resulting in 10x faster page loads.",
  },
  {
    question: "Do I need to configure anything?",
    answer:
      "No! Blitz Cache works with zero configuration. Simply activate the plugin and it will start caching your pages immediately. Advanced users can customize settings, but it's not required.",
  },
  {
    question: "Is Blitz Cache compatible with my theme?",
    answer:
      "Yes! Blitz Cache is compatible with all WordPress themes and plugins. It automatically handles WooCommerce, EDD, LearnDash, and other popular plugins without any configuration.",
  },
  {
    question: "What is Cloudflare integration?",
    answer:
      "Blitz Cache can automatically purge your Cloudflare cache when content changes. This ensures your visitors always see the latest content while benefiting from Cloudflare's global CDN.",
  },
  {
    question: "Will it cache dynamic content like shopping carts?",
    answer:
      "No, Blitz Cache is smart enough to exclude dynamic pages like shopping carts, checkout, and user account pages. It only caches publicly viewable pages that are safe to cache.",
  },
  {
    question: "Is Blitz Cache free?",
    answer:
      "Yes! Blitz Cache is 100% free and open source. There are no hidden fees, subscriptions, or premium versions. It's completely free forever.",
  },
  {
    question: "How do I install Blitz Cache?",
    answer:
      "You can install Blitz Cache from your WordPress admin dashboard, download it from WordPress.org, or get the latest version from our GitHub repository. Check our documentation for detailed instructions.",
  },
  {
    question: "Does it work with WordPress Multisite?",
    answer:
      "Yes, Blitz Cache is fully compatible with WordPress Multisite installations. Each site in the network can have its own caching configuration.",
  },
  {
    question: "Where can I get support?",
    answer:
      "You can get support through our GitHub repository, WordPress.org support forum, or by opening an issue on GitHub. We also have comprehensive documentation available.",
  },
]

export function FAQ() {
  return (
    <section id="faq" className="py-20 md:py-32">
      <div className="container mx-auto max-w-4xl px-4">
        <div className="text-center mb-16">
          <h2 className="text-3xl md:text-4xl font-bold mb-4">
            Frequently Asked <span className="gradient-text">Questions</span>
          </h2>
          <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
            Everything you need to know about Blitz Cache. Can't find the answer you're looking for? Reach out to us.
          </p>
        </div>

        <Accordion type="single" collapsible className="w-full">
          {faqs.map((faq, index) => (
            <AccordionItem key={index} value={`item-${index}`} className="border-b">
              <AccordionTrigger className="text-left hover:no-underline hover:text-purple-600">
                {faq.question}
              </AccordionTrigger>
              <AccordionContent className="text-muted-foreground">
                {faq.answer}
              </AccordionContent>
            </AccordionItem>
          ))}
        </Accordion>
      </div>
    </section>
  )
}
