import React from 'react';
import { Button } from '@/components/ui/button';
import { Calendar, Settings, TrendingUp, ArrowRight } from 'lucide-react';
import { motion } from 'framer-motion';

const HowItWorksSection = () => {
  const steps = [
    {
      icon: Calendar,
      title: "Book Your Strategy Call",
      description: "30-minute deep-dive into your current growth challenges and target ICP",
      timeline: "Today"
    },
    {
      icon: Settings,
      title: "Rapid System Setup",
      description: "We build and integrate your AI-powered demand generation system",
      timeline: "Week 1-2"
    },
    {
      icon: TrendingUp,
      title: "Measurable Growth Starts",
      description: "Qualified leads flow into your pipeline with full visibility and reporting",
      timeline: "Week 3+"
    }
  ];

  return (
    <section className="py-20 bg-white">
      <div className="container mx-auto px-4 md:px-6">
        <motion.div 
          className="text-center mb-16"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">
            From Call to <span className="gradient-text">Qualified Pipeline</span> in 3 Weeks
          </h2>
          <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
            No lengthy onboarding. No complex implementations. Just fast, measurable results.
          </p>
        </motion.div>
        
        <div className="grid md:grid-cols-3 gap-8 mb-12">
          {steps.map((step, index) => (
            <motion.div 
              key={index} 
              className="text-center"
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.2 }}
              viewport={{ once: true }}
            >
              <div className="relative mb-6">
                <div className="w-20 h-20 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center mx-auto mb-4">
                  <step.icon className="w-10 h-10 text-white" />
                </div>
                <div className="absolute -top-2 -right-2 bg-venture-100 text-venture-700 text-sm font-bold px-3 py-1 rounded-full">
                  {step.timeline}
                </div>
              </div>
              <h3 className="text-2xl font-bold text-black mb-4">{step.title}</h3>
              <p className="text-foreground/80 leading-relaxed">{step.description}</p>
            </motion.div>
          ))}
        </div>
        
        <motion.div 
          className="text-center"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <Button 
            size="lg" 
            className="bg-booming-600 hover:bg-booming-700 text-white text-lg px-8 py-4 font-semibold"
            onClick={() => window.open('https://meet.brevo.com/ben-verschuur', '_blank')}
          >
            Start Your Growth Transformation
            <ArrowRight className="ml-2 w-5 h-5" />
          </Button>
          <p className="text-foreground/60 mt-4 text-sm">
            Free strategy session • No commitment required • Actionable insights guaranteed
          </p>
        </motion.div>
      </div>
    </section>
  );
};

export default HowItWorksSection;