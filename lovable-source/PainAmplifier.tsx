import React from 'react';
import { AlertTriangle } from 'lucide-react';
import { motion } from 'framer-motion';

const PainAmplifier = () => {
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
          <AlertTriangle className="w-12 h-12 text-red-500 mx-auto mb-4" />
          <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">
            Sound Familiar?
          </h2>
          <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
            The daily frustrations of hitting aggressive growth targets with limited resources.
          </p>
        </motion.div>
        
        <div className="grid md:grid-cols-2 gap-8">
          <motion.div 
            className="space-y-6"
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <div className="bg-red-50 border border-red-200 rounded-xl p-6">
              <h3 className="font-bold text-lg mb-3 text-red-700">Monday Morning Reality</h3>
              <p className="text-red-600">
                You're staring at your pipeline dashboard. <strong>Again.</strong> The numbers haven't moved. 
                Your scattered tools are producing leads, but they're either unqualified or going cold 
                before sales can follow up.
              </p>
            </div>
            
            <div className="bg-red-50 border border-red-200 rounded-xl p-6">
              <h3 className="font-bold text-lg mb-3 text-red-700">The Daily Grind</h3>
              <p className="text-red-600">
                Your team is drowning in manual tasks. LinkedIn prospecting, email sequences, 
                lead scoring—everything requires hands-on work that <strong> doesn't scale </strong> 
                and burns through your budget.
              </p>
            </div>
          </motion.div>
          
          <motion.div 
            className="space-y-6"
            initial={{ opacity: 0, x: 30 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            viewport={{ once: true }}
          >
            <div className="bg-red-50 border border-red-200 rounded-xl p-6">
              <h3 className="font-bold text-lg mb-3 text-red-700">Board Meeting Pressure</h3>
              <p className="text-red-600">
                The board wants aggressive growth targets met with <strong>limited resources.</strong> 
                Marketing and sales aren't aligned. Deals are slow. You need predictable demand 
                generation, not guesswork.
              </p>
            </div>
            
            <div className="bg-red-50 border border-red-200 rounded-xl p-6">
              <h3 className="font-bold text-lg mb-3 text-red-700">Time Running Out</h3>
              <p className="text-red-600">
                Each quarter that passes without a systematic approach puts you further behind. 
                Your competitors are moving faster. You need <strong>measurable results now.</strong>
              </p>
            </div>
          </motion.div>
        </div>
      </div>
    </section>
  );
};

export default PainAmplifier;