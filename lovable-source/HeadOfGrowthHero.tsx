import React from 'react';
import { Button } from '@/components/ui/button';
import { ArrowRight, Star } from 'lucide-react';
import { motion } from 'framer-motion';

const HeadOfGrowthHero = () => {
  return (
    <section className="relative pt-32 pb-20 overflow-hidden">
      <div className="absolute -top-[30%] -right-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-booming-200/40 to-venture-300/40 blur-3xl"></div>
      
      <div className="container mx-auto px-4 md:px-6 relative">
        <motion.div 
          className="text-center max-w-5xl mx-auto"
          initial={{ opacity: 0, y: 50 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8 }}
        >
          <div className="space-y-8">
            <div className="inline-flex items-center gap-2 bg-gradient-to-r from-booming-100 to-venture-100 text-booming-700 px-4 py-2 rounded-full text-sm font-medium mb-6">
              <Star className="h-4 w-4" />
              For Heads of Growth Only
            </div>
            
            <h1 className="text-4xl md:text-6xl font-bold text-black leading-tight">
              Turn Your Growth Targets Into<br/>
              <span className="gradient-text">Predictable Pipeline</span>
            </h1>
            
            <p className="text-xl text-foreground/80 max-w-4xl mx-auto leading-relaxed">
              Stop struggling with scattered tools and manual outreach. Get a fully automated, 
              AI-powered demand generation system that delivers <strong>qualified leads in weeks, not months.</strong>
            </p>
            
            <div className="pt-8">
              <Button 
                size="lg" 
                className="bg-booming-600 hover:bg-booming-700 text-white text-lg px-8 py-4 font-semibold"
                onClick={() => window.open('https://meet.brevo.com/ben-verschuur', '_blank')}
              >
                Book Your Growth Strategy Call
                <ArrowRight className="ml-2 w-5 h-5" />
              </Button>
            </div>
            
            <p className="text-foreground/60 text-sm pt-4">
              Free 30-minute strategy session • No sales pitch • Actionable insights guaranteed
            </p>
          </div>
        </motion.div>
      </div>
    </section>
  );
};

export default HeadOfGrowthHero;