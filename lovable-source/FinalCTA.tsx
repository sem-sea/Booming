import React from 'react';
import { Button } from '@/components/ui/button';
import { CheckCircle, ArrowRight, Calendar } from 'lucide-react';
import { motion } from 'framer-motion';

const FinalCTA = () => {
  const benefits = [
    "Fully automated multichannel demand generation (LinkedIn + Email + Intent Data)",
    "AI-driven ICP targeting and lead scoring—only qualified prospects",
    "End-to-end campaign design, execution, and optimization",
    "Full CRM integration with real-time reporting dashboards",
    "Measurable results in weeks, not months—pipeline you can count on"
  ];

  return (
    <section className="py-20 bg-gradient-to-br from-booming-600 to-venture-600 text-white">
      <div className="container mx-auto px-4 md:px-6">
        <motion.div 
          className="text-center mb-12"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <h2 className="text-3xl md:text-4xl font-bold mb-6 leading-tight">
            Ready to Turn Growth Targets Into 
            <span className="text-venture-100"> Predictable Pipeline</span>?
          </h2>
          <p className="text-xl text-white/90 max-w-3xl mx-auto mb-8">
            Stop struggling with manual outreach and scattered tools. Get the AI-powered demand generation 
            system that growth leaders trust for consistent, measurable results.
          </p>
        </motion.div>
        
        <div className="grid md:grid-cols-2 gap-12 items-center">
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h3 className="text-2xl font-bold mb-6">What You Get in Your Strategy Session:</h3>
            <ul className="space-y-4">
              {benefits.map((benefit, index) => (
                <li key={index} className="flex items-start gap-3">
                  <CheckCircle className="w-6 h-6 text-venture-100 flex-shrink-0 mt-1" />
                  <span className="text-white/90">{benefit}</span>
                </li>
              ))}
            </ul>
          </motion.div>
          
          <motion.div 
            className="bg-white/10 backdrop-blur-sm rounded-xl p-8 text-center border border-white/20"
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            viewport={{ once: true }}
          >
            <Calendar className="w-16 h-16 text-venture-100 mx-auto mb-6" />
            <h3 className="text-2xl font-bold mb-4">Book Your Free Strategy Session</h3>
            <p className="text-white/80 mb-6">
              30 minutes • No sales pitch • Actionable growth insights • Limited availability
            </p>
            
            <Button 
              size="lg" 
              className="w-full bg-white text-booming-600 hover:bg-white/90 text-xl py-5 font-bold mb-4"
              onClick={() => window.open('https://meet.brevo.com/ben-verschuur', '_blank')}
            >
              Book Your Growth Strategy Call
              <ArrowRight className="ml-2 w-6 h-6" />
            </Button>
            
            <p className="text-white/70 text-sm">
              Join growth leaders who've transformed their pipeline with predictable, AI-powered demand generation
            </p>
          </motion.div>
        </div>
        
        <motion.div 
          className="text-center mt-16"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <p className="text-white/80 text-lg">
            <strong>Warning:</strong> We only work with 5 new clients per month. December slots are limited.
          </p>
        </motion.div>
      </div>
    </section>
  );
};

export default FinalCTA;