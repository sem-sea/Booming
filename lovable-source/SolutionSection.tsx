import React from 'react';
import { Button } from '@/components/ui/button';
import { CheckCircle, ArrowRight } from 'lucide-react';
import { motion } from 'framer-motion';

const SolutionSection = () => {
  const solutions = [
    {
      title: "Fully Automated Multichannel Demand Generation",
      description: "LinkedIn, email, and intent data working together—no manual work required"
    },
    {
      title: "AI-Driven ICP Targeting & Lead Scoring",
      description: "Only qualified prospects enter your pipeline, pre-scored and ready for sales"
    },
    {
      title: "End-to-End Campaign Design & Optimization",
      description: "We handle strategy, execution, and continuous improvement—you get results"
    },
    {
      title: "Full CRM Integration with Clear Reporting",
      description: "Real-time dashboards showing pipeline progression and ROI at every stage"
    },
    {
      title: "Fast Implementation & Measurable Results",
      description: "Live system in weeks, not months—with clear KPIs and growth metrics"
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
            What If You Could <span className="gradient-text">Automate Growth</span>?
          </h2>
          <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
            Booming Venture eliminates every friction point in your demand generation. 
            Here's exactly how we transform your growth engine:
          </p>
        </motion.div>
        
        <div className="space-y-6 mb-12">
          {solutions.map((solution, index) => (
            <motion.div 
              key={index} 
              className="bg-white rounded-xl p-8 shadow-lg border border-booming-100"
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.1 }}
              viewport={{ once: true }}
            >
              <div className="flex items-start gap-4">
                <CheckCircle className="w-6 h-6 text-venture-500 flex-shrink-0 mt-1" />
                <div>
                  <h3 className="text-xl font-bold text-black mb-2">{solution.title}</h3>
                  <p className="text-foreground/80">{solution.description}</p>
                </div>
              </div>
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
            See How This Works For Your Business
            <ArrowRight className="ml-2 w-5 h-5" />
          </Button>
        </motion.div>
      </div>
    </section>
  );
};

export default SolutionSection;