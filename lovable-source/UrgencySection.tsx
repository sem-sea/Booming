import React from 'react';
import { Button } from '@/components/ui/button';
import { AlertCircle, ArrowRight, Calendar } from 'lucide-react';
import { motion } from 'framer-motion';

const UrgencySection = () => {
  return (
    <section className="py-20 bg-white">
      <div className="container mx-auto px-4 md:px-6">
        <motion.div 
          className="text-center mb-12"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <AlertCircle className="w-16 h-16 text-red-500 mx-auto mb-6" />
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">
            Every Quarter You Wait Is <span className="text-red-600">Revenue Lost</span>
          </h2>
        </motion.div>
        
        <div className="grid md:grid-cols-3 gap-8 mb-12">
          {[
            { title: "Q4 Pressure", desc: "Board expectations don't wait. Your competitors are implementing AI-powered systems right now." },
            { title: "Compound Effect", desc: "Every month without systematic demand generation costs you exponential growth in future quarters." },
            { title: "First-Mover Advantage", desc: "Your market window is closing. Early adopters of AI-powered demand gen are dominating pipeline." }
          ].map((item, index) => (
            <motion.div 
              key={index}
              className="text-center p-6"
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <div className="text-2xl font-bold text-red-600 mb-3">{item.title}</div>
              <p className="text-foreground/80">
                {item.desc}
              </p>
            </motion.div>
          ))}
        </div>
        
        <motion.div 
          className="bg-gradient-to-br from-booming-50 to-venture-50 border-2 border-booming-200 rounded-xl p-8 text-center"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
        >
          <Calendar className="w-12 h-12 text-booming-600 mx-auto mb-4" />
          <h3 className="text-2xl font-bold text-black mb-4">
            Limited Strategy Sessions Available This Month
          </h3>
          <p className="text-foreground/80 mb-6 text-lg">
            We only work with <strong>5 new growth leaders per month</strong> to ensure hands-on implementation success. 
            December slots are filling fast.
          </p>
          
          <Button 
            size="lg" 
            className="bg-booming-600 hover:bg-booming-700 text-white text-xl px-10 py-5 font-bold"
            onClick={() => window.open('https://meet.brevo.com/ben-verschuur', '_blank')}
          >
            Secure Your Strategy Session Now
            <ArrowRight className="ml-2 w-6 h-6" />
          </Button>
          
          <p className="text-sm text-foreground/60 mt-4">
            Free 30-minute session • No sales pitch • Book before slots fill up
          </p>
        </motion.div>
      </div>
    </section>
  );
};

export default UrgencySection;