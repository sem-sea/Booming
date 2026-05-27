import { Button } from "@/components/ui/button";
import { ArrowRight, Download, CheckCircle, Star, Users, TrendingUp, Zap, Search, Mail, Settings, BarChart, Target } from "lucide-react";
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { toast } from "@/hooks/use-toast";
import { motion } from "framer-motion";
import { addContactToBrevo } from "@/services/brevoService";
import { checkForSpam } from "@/utils/spamFilter";
import UnifyFlywheel from "@/components/unify/UnifyFlywheel";
import MarketClarityMap from "@/components/unify/MarketClarityMap";
import NurtureTree from "@/components/unify/NurtureTree";
import AutomationFlow from "@/components/unify/AutomationFlow";
import ForecastPyramid from "@/components/unify/ForecastPyramid";
import LaunchTracker from "@/components/unify/LaunchTracker";
import ModuleCards from "@/components/unify/ModuleCards";

const UnifyFramework = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleDownload = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need your email to provide you with the UNIFY Framework™ guide",
        variant: "destructive",
      });
      return;
    }
    
    const spamCheck = checkForSpam(email);
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in UNIFY Framework:', spamCheck);
      toast({
        title: "Invalid email",
        description: "Please enter a valid business email address.",
        variant: "destructive",
      });
      return;
    }
    
    setIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email,
        attributes: {
          LEAD_SOURCE: "UNIFY Framework Landing Page"
        },
        listIds: [5] // UNIFY Framework list ID 5
      });

      if (success) {
        toast({
          title: "Success! 🚀",
          description: "Your UNIFY Framework™ guide is being prepared. Check your email in a few minutes.",
          variant: "default",
        });
        setEmail("");
      } else {
        throw new Error("Failed to add contact");
      }
    } catch (error) {
      console.error("Error submitting form:", error);
      toast({
        title: "Something went wrong",
        description: "Please try again or contact support.",
        variant: "destructive",
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-booming-50 to-venture-50">
      {/* Hero Section */}
      <section className="relative pt-32 pb-20 overflow-hidden">
        <div className="absolute -top-[30%] -right-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-booming-200/40 to-venture-300/40 blur-3xl"></div>
        
        <div className="container mx-auto px-4 md:px-6 relative">
          <div className="grid md:grid-cols-2 gap-12 items-center">
            <motion.div 
              className="space-y-8"
              initial={{ opacity: 0, x: -50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.7 }}
            >
              <div className="space-y-4">
                <div className="inline-flex items-center gap-2 bg-gradient-to-r from-booming-100 to-venture-100 text-booming-700 px-4 py-2 rounded-full text-sm font-medium">
                  <Star className="h-4 w-4" />
                  Introducing UNIFY Framework™
                </div>
                
                <h1 className="text-4xl md:text-6xl font-bold text-black leading-tight">
                  One System.<br/>
                  <span className="gradient-text">Five Phases.</span><br/>
                  Zero Guesswork.
                </h1>
                
                <p className="text-xl text-foreground/80 max-w-lg">
                  The UNIFY Framework™ helps you implement AI into your marketing with systems, not more tools.
                </p>
              </div>

              <form onSubmit={handleDownload} className="bg-white/80 backdrop-blur-sm p-6 rounded-xl border border-booming-100 shadow-lg max-w-md">
                <h3 className="text-lg font-bold mb-2">Get the Free UNIFY Framework™ Guide</h3>
                <p className="text-sm text-foreground/70 mb-4">Complete framework with implementation templates</p>
                
                <div className="space-y-4">
                  <Input
                    type="email"
                    placeholder="Enter your business email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full"
                  />
                  <Button type="submit" disabled={isSubmitting} className="w-full bg-booming-600 hover:bg-booming-700 text-white">
                    <Download className="mr-2 h-4 w-4" />
                    {isSubmitting ? "Preparing..." : "Download the Free UNIFY Guide"}
                  </Button>
                  
                  <div className="flex items-center gap-2 text-sm text-foreground/60">
                    <CheckCircle className="h-4 w-4 text-venture-500" />
                    <span>No spam. Unsubscribe anytime.</span>
                  </div>
                </div>
              </form>
            </motion.div>

            <motion.div 
              className="relative"
              initial={{ opacity: 0, x: 50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.7, delay: 0.2 }}
            >
              <UnifyFlywheel />
            </motion.div>
          </div>
        </div>
      </section>

      {/* From Friction to Flow Section */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">From Friction to Flow</h2>
            <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
              Marketing often feels disconnected. Campaigns launch, tools don't sync, leads go cold. 
              UNIFY connects all moving parts into one operating system.
            </p>
          </motion.div>

          <div className="grid md:grid-cols-2 gap-12 items-center">
            <motion.div 
              className="space-y-6"
              initial={{ opacity: 0, x: -30 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.6 }}
              viewport={{ once: true }}
            >
              <div className="bg-red-50 border border-red-200 rounded-xl p-6">
                <h3 className="text-xl font-bold text-red-700 mb-4">Before: Disjointed Systems</h3>
                <ul className="space-y-3">
                  <li className="flex items-start gap-3">
                    <div className="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                    <span>Tools that don't talk to each other</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <div className="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                    <span>Leads falling through the cracks</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <div className="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                    <span>Manual processes eating up time</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <div className="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                    <span>No clear visibility into what's working</span>
                  </li>
                </ul>
              </div>
            </motion.div>

            <motion.div 
              className="space-y-6"
              initial={{ opacity: 0, x: 30 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.6, delay: 0.2 }}
              viewport={{ once: true }}
            >
              <div className="bg-gradient-to-br from-booming-50 to-venture-50 border border-booming-200 rounded-xl p-6">
                <h3 className="text-xl font-bold gradient-text mb-4">After: UNIFY Framework™</h3>
                <ul className="space-y-3">
                  <li className="flex items-start gap-3">
                    <CheckCircle className="h-5 w-5 text-venture-500 mt-0.5 flex-shrink-0" />
                    <span>All tools connected in one central hub</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <CheckCircle className="h-5 w-5 text-venture-500 mt-0.5 flex-shrink-0" />
                    <span>Automated lead nurturing and follow-up</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <CheckCircle className="h-5 w-5 text-venture-500 mt-0.5 flex-shrink-0" />
                    <span>AI-powered optimization and insights</span>
                  </li>
                  <li className="flex items-start gap-3">
                    <CheckCircle className="h-5 w-5 text-venture-500 mt-0.5 flex-shrink-0" />
                    <span>Complete visibility and control</span>
                  </li>
                </ul>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      {/* Market Clarity Section */}
      <section className="py-20 bg-gradient-to-r from-booming-50 to-venture-50">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">🎯 Market Clarity Map</h2>
            <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
              Understand your buyers' behavior, objections, and decision triggers to build systems that convert.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <MarketClarityMap />
          </motion.div>
        </div>
      </section>

      {/* The Five Phases Section */}
      <section className="py-20 bg-gradient-to-r from-booming-50 to-venture-50">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">🔁 Overview: The 5 Phases of the UNIFY Framework™</h2>
            <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
              Each phase builds on the last, creating a comprehensive marketing system that scales with your business.
            </p>
          </motion.div>

          <div className="space-y-8">
            {[
              { 
                phase: "1", 
                title: "Understand — Strategic Clarity Before Execution", 
                desc: "Helps you deeply understand your market, persona behavior, buying triggers, objections, and funnel friction points.",
                why: "You can't build a system that converts if you don't know where the friction lives.",
                tools: "CRM analysis, review scraping, sentiment clustering, funnel heatmaps.",
                icon: Search,
                component: <MarketClarityMap />
              },
              { 
                phase: "2", 
                title: "Nurture — Intent-Driven Progression Through the Funnel", 
                desc: "Builds nurture sequences tailored to intent and funnel stage (TOFU → MOFU → BOFU).",
                why: "Builds trust, aligns with buyer psychology, and avoids spammy sequences.",
                tools: "Prompt libraries, nurture flow builder, retargeting triggers.",
                icon: Mail,
                component: <NurtureTree />
              },
              { 
                phase: "3", 
                title: "Integrate — Building the Operational Backbone", 
                desc: "Connects all tools into a real-time system: CRM, Slack, AI, forms, ads, sequences.",
                why: "Eliminates manual work, improves speed and timing, increases lead quality.",
                tools: "Zapier, Make, HubSpot, GPT, Slack automations.",
                icon: Settings,
                component: <AutomationFlow />
              },
              { 
                phase: "4", 
                title: "Forecast — Predict ROI Before You Spend", 
                desc: "Build models that simulate ROI before campaign launch.",
                why: "You plan growth using signal-based ROI instead of guesswork.",
                tools: "ROI calculators, CAC/LTV dashboards, scenario planners.",
                icon: BarChart,
                component: <ForecastPyramid />
              },
              { 
                phase: "5", 
                title: "Yield — Execute, Learn, and Optimize in Loops", 
                desc: "Activates everything through a 30-day growth plan. Learn from actions, test, iterate, repeat.",
                why: "This is where real-world momentum and compounding growth begins.",
                tools: "A/B dashboards, growth loop trackers, feedback forms, Slack signals.",
                icon: Target,
                component: <LaunchTracker />
              }
            ].map((item, index) => (
              <motion.div 
                key={index}
                className="bg-white rounded-xl p-8 shadow-lg"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                viewport={{ once: true }}
              >
                <div className="flex items-start gap-6 mb-8">
                  <div className="w-16 h-16 bg-gradient-to-r from-booming-500 to-venture-500 text-white rounded-full flex items-center justify-center text-2xl font-bold flex-shrink-0">
                    {item.phase}
                  </div>
                  <div className="flex-1">
                    <div className="flex items-center gap-4 mb-4">
                      <item.icon className="h-8 w-8 text-booming-600" />
                      <h3 className="text-2xl font-bold text-black">{item.title}</h3>
                    </div>
                    <div className="space-y-4">
                      <div>
                        <p className="text-lg text-foreground/80 mb-2"><strong>What it does:</strong> {item.desc}</p>
                      </div>
                      <div>
                        <p className="text-lg text-foreground/80 mb-2"><strong>Why it matters:</strong> {item.why}</p>
                      </div>
                      <div>
                        <p className="text-lg text-foreground/80"><strong>Key tools:</strong> {item.tools}</p>
                      </div>
                    </div>
                  </div>
                </div>
                
                {/* Interactive Component */}
                <div className="mt-8 p-6 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg">
                  {item.component}
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Module Overview Section */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">📚 What's Inside the UNIFY Guide</h2>
            <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
              Four comprehensive modules that take you from strategy to execution in 30 days.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <ModuleCards />
          </motion.div>
        </div>
      </section>

      {/* Results Section */}
      <section className="py-20 bg-white">
        <div className="container mx-auto px-4 md:px-6">
          <motion.div 
            className="text-center mb-16"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold text-black mb-6">Potential Result</h2>
            <p className="text-xl text-foreground/80 max-w-3xl mx-auto">
              Companies using the UNIFY Framework™ could expect improvements in efficiency and results.
            </p>
          </motion.div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              { metric: "48%", label: "Average Revenue Growth", desc: "Within 6 months of implementation" },
              { metric: "67%", label: "Time Savings", desc: "Less manual work, more strategic focus" },
              { metric: "3.2x", label: "Lead Quality Improvement", desc: "Better targeting and nurturing" }
            ].map((item, index) => (
              <motion.div 
                key={index}
                className="text-center bg-gradient-to-br from-booming-50 to-venture-50 rounded-xl p-8"
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                viewport={{ once: true }}
              >
                <div className="text-4xl font-bold gradient-text mb-2">{item.metric}</div>
                <h3 className="text-lg font-semibold text-black mb-2">{item.label}</h3>
                <p className="text-foreground/70">{item.desc}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Final CTA Section */}
      <section className="py-20 bg-gradient-to-r from-booming-600 to-venture-600 text-white">
        <div className="container mx-auto px-4 md:px-6 text-center">
          <motion.div 
            className="max-w-3xl mx-auto space-y-8"
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            viewport={{ once: true }}
          >
            <h2 className="text-3xl md:text-4xl font-bold">Ready to UNIFY Your Marketing?</h2>
            <p className="text-xl opacity-90">
              Download the complete UNIFY Framework™ guide and start transforming your marketing system today.
            </p>
            
            <div className="bg-white/10 backdrop-blur-sm rounded-xl p-8 max-w-md mx-auto">
              <form onSubmit={handleDownload} className="space-y-4">
                <Input
                  type="email"
                  placeholder="Enter your business email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="bg-white text-black"
                />
                <Button 
                  type="submit" 
                  disabled={isSubmitting} 
                  className="w-full bg-white text-booming-600 hover:bg-gray-100"
                >
                  <Download className="mr-2 h-4 w-4" />
                  {isSubmitting ? "Preparing..." : "Get Your Free UNIFY™ Guide"}
                </Button>
              </form>
            </div>
          </motion.div>
        </div>
      </section>
    </div>
  );
};

export default UnifyFramework;
