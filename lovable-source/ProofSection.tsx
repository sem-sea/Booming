import React from 'react';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { TrendingUp, Users, Clock } from 'lucide-react';
import { motion } from 'framer-motion';

const ProofSection = () => {
  const results = [
    {
      metric: "247%",
      description: "Increase in qualified leads",
      icon: TrendingUp,
      color: "text-venture-600"
    },
    {
      metric: "18 Days",
      description: "Average setup to first qualified lead",
      icon: Clock,
      color: "text-booming-600"
    },
    {
      metric: "94%",
      description: "Lead-to-opportunity conversion rate",
      icon: Users,
      color: "text-venture-600"
    }
  ];

  const testimonials = [
    {
      quote: "We went from manual LinkedIn outreach to a fully automated system generating 40+ qualified SQLs per month. Game changer.",
      name: "Sarah Chen",
      title: "Head of Growth, TechScale",
      industry: "B2B SaaS"
    },
    {
      quote: "Finally, predictable pipeline. Our board meetings went from damage control to celebrating consistent growth metrics.",
      name: "Marcus Rodriguez",
      title: "VP Growth, InnovateCorp",
      industry: "FinTech"
    }
  ];

  return (
    <section className="py-20 bg-gradient-to-r from-booming-50 to-venture-50">
      <div className="container mx-auto px-4 md:px-6">
        <motion.div 
          className="text-center mb-16"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">
            Real Results from <span className="gradient-text">Real Growth Leaders</span>
          </h2>
          <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
            Don't take our word for it. Here's what happens when Heads of Growth implement our system:
          </p>
        </motion.div>
        
        {/* Results Metrics */}
        <div className="grid md:grid-cols-3 gap-8 mb-16">
          {results.map((result, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <Card className="text-center p-8 bg-white border-2 border-booming-100 hover:border-booming-200 transition-colors shadow-lg">
                <CardContent className="pt-6">
                  <result.icon className={`w-12 h-12 mx-auto mb-4 ${result.color}`} />
                  <div className={`text-4xl font-bold mb-2 ${result.color}`}>{result.metric}</div>
                  <p className="text-foreground/80 font-medium">{result.description}</p>
                </CardContent>
              </Card>
            </motion.div>
          ))}
        </div>
        
        {/* Testimonials */}
        <div className="grid md:grid-cols-2 gap-8">
          {testimonials.map((testimonial, index) => (
            <motion.div
              key={index}
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.2 }}
              viewport={{ once: true }}
            >
              <Card className="p-8 bg-white border border-booming-100 shadow-lg">
                <CardContent className="pt-6">
                  <blockquote className="text-lg text-black mb-6 leading-relaxed">
                    "{testimonial.quote}"
                  </blockquote>
                  <div className="flex items-center justify-between">
                    <div>
                      <div className="font-bold text-black">{testimonial.name}</div>
                      <div className="text-sm text-foreground/70">{testimonial.title}</div>
                    </div>
                    <Badge variant="secondary" className="bg-venture-100 text-venture-700">{testimonial.industry}</Badge>
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          ))}
        </div>
        
        {/* Trust Signals */}
        <motion.div 
          className="text-center mt-12"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <p className="text-foreground/70 mb-4">Trusted by growth leaders at:</p>
          <div className="flex flex-wrap justify-center gap-8 items-center opacity-60">
            <div className="text-lg font-semibold text-black">TechScale • InnovateCorp • GrowthCo • ScaleUp</div>
          </div>
        </motion.div>
      </div>
    </section>
  );
};

export default ProofSection;