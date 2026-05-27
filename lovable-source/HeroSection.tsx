
import { Button } from "@/components/ui/button";
import { ArrowRight, Download, CheckCircle } from "lucide-react";
import { useState } from "react";
import { Input } from "@/components/ui/input";
import { toast } from "@/hooks/use-toast";
import AnimatedScene from "@/components/3d/AnimatedScene";
import { motion } from "framer-motion";
import { addContactToBrevo } from "@/services/brevoService";
import { downloadGrowthGuidePDF } from "@/components/growth-guide/GrowthGuidePDF";
import { checkForSpam } from "@/utils/spamFilter";

const HeroSection = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleDownload = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need your email to provide you with the growth strategy guide",
        variant: "destructive",
      });
      return;
    }
    
    // Spam filtering
    const spamCheck = checkForSpam(email);
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in hero section:', spamCheck);
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
          LEAD_SOURCE: "Hero Section - Growth Guide"
        },
        listIds: [2] // Free Strategy Guide list
      });

      if (success) {
        // Generate and download PDF immediately
        downloadGrowthGuidePDF();
        
        toast({
          title: "Thank you!",
          description: "Your growth strategy guide is now downloading. Check your downloads folder.",
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

  const scrollToContact = () => {
    const contactSection = document.querySelector('#contact');
    if (contactSection) {
      contactSection.scrollIntoView({ behavior: 'smooth' });
    }
  };

  return (
    <section id="home" className="relative pt-28 pb-16 md:pt-32 md:pb-24 overflow-hidden">
      {/* Backdrop Glow Elements */}
      <div className="absolute -top-[30%] -right-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-booming-200/40 to-venture-300/40 blur-3xl"></div>
      <div className="absolute -bottom-[30%] -left-[15%] w-[70%] h-[70%] rounded-full bg-gradient-to-bl from-venture-200/40 to-booming-300/40 blur-3xl"></div>
      
      {/* Background animation without scroll interaction */}
      <div className="absolute inset-0 z-0">
        <AnimatedScene className="w-full h-full" />
      </div>
      
      <div className="container mx-auto px-4 md:px-6 relative z-10">
        <div className="flex flex-col md:flex-row items-center">
          <div className="md:w-1/2 md:pr-8 space-y-6">
            <motion.h1 
              className="font-bold tracking-tight text-3xl md:text-5xl lg:text-6xl text-black"
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.4, duration: 0.7 }}
            >
              <span className="block">
                Smarter growth. Clear strategy. Creative performance.
              </span>
            </motion.h1>
            
            <motion.p 
              className="text-xl text-foreground/80 max-w-lg backdrop-blur-sm bg-white/30 p-4 rounded-xl"
              initial={{ opacity: 0, y: 40 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.6, duration: 0.7 }}
            >
              We implement AI-powered marketing that delivers results.
              <b> Performance. Personality. Powered by AI.</b>            </motion.p>
            
            <motion.div 
              className="bg-white/70 backdrop-blur-sm p-6 rounded-xl border border-booming-100 shadow-lg"
              initial={{ opacity: 0, y: 50 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.8, duration: 0.7 }}
            >
              <h3 className="text-lg font-bold mb-2">Get Our Free Growth Strategy Guide</h3>
              <p className="text-sm text-foreground/70 mb-4">Learn the exact strategies our clients use to achieve 48%+ growth</p>
              
              <form onSubmit={handleDownload} className="space-y-4">
                <div className="flex flex-col sm:flex-row gap-2">
                  <Input
                    type="email"
                    placeholder="Enter your business email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="flex-grow"
                  />
                  <Button type="submit" disabled={isSubmitting} className="bg-booming-600 hover:bg-booming-700 text-white">
                    <Download className="mr-2 h-4 w-4" />
                    {isSubmitting ? "Preparing..." : "Download Now"}
                  </Button>
                </div>
                
                <div className="flex flex-col gap-2 text-sm">
                  <div className="flex items-center gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-500" />
                    <span>Actionable growth strategies</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-500" />
                    <span>AI implementation guide</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-500" />
                    <span>ROI calculation templates</span>
                  </div>
                </div>
              </form>
            </motion.div>
            
            <motion.div 
              className="pt-4"
              initial={{ opacity: 0, y: 60 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 1, duration: 0.7 }}
            >
              <Button 
                onClick={scrollToContact}
                className="bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700 text-white font-medium px-6 py-6 h-auto glow-on-hover"
              >
                Start the Journey
                <ArrowRight className="ml-2 h-5 w-5" />
              </Button>
              
              <Button
                variant="outline"
                className="ml-4 text-black border-black hover:bg-black hover:text-white"
                onClick={() => window.location.href = 'mailto:info@boomingventure.com'}
              >
                Email Us Directly
              </Button>
            </motion.div>
          </div>
          
          <div className="md:w-1/2 mt-10 md:mt-0">
            <motion.div 
              className="relative"
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              transition={{ delay: 0.5, duration: 1 }}
            >
              <div className="absolute -top-6 -left-6 w-24 h-24 bg-gradient-to-br from-booming-100 to-venture-200 rounded-xl rotate-12 animate-pulse"></div>
              <div className="absolute -bottom-6 -right-6 w-24 h-24 bg-gradient-to-tr from-venture-100 to-booming-200 rounded-xl -rotate-12 animate-pulse" style={{ animationDelay: "1s" }}></div>
              
              <div className="absolute inset-0 bg-gradient-to-tr from-booming-200/70 to-venture-200/70 rounded-lg transform rotate-3 backdrop-blur-md"></div>
              <div className="relative flex flex-col bg-white/80 backdrop-blur-sm p-6 md:p-8 rounded-lg shadow-lg">
                <div className="mb-6 rounded-md overflow-hidden">
                  <img 
                    src="/lovable-uploads/12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4.png" 
                    alt="Modern business team collaborating around a table with laptops in a bright office space"
                    className="w-full h-auto object-cover rounded-md"
                  />
                </div>
                
                <div className="grid grid-cols-2 gap-4">
                  <motion.div 
                    className="bg-gradient-to-br from-booming-50/90 to-booming-100/90 p-4 rounded-md transition-transform hover:scale-105 duration-300 backdrop-blur-sm"
                    whileHover={{ 
                      boxShadow: "0 0 20px rgba(2, 132, 199, 0.4)",
                      y: -5
                    }}
                  >
                    <h3 className="text-booming-700 text-lg font-semibold">97%</h3>
                    <p className="text-sm text-foreground/70">Client satisfaction</p>
                  </motion.div>
                  <motion.div 
                    className="bg-gradient-to-br from-venture-50/90 to-venture-100/90 p-4 rounded-md transition-transform hover:scale-105 duration-300 backdrop-blur-sm"
                    whileHover={{ 
                      boxShadow: "0 0 20px rgba(13, 148, 136, 0.4)",
                      y: -5
                    }}
                  >
                    <h3 className="text-venture-700 text-lg font-semibold">+48%</h3>
                    <p className="text-sm text-foreground/70">Average growth</p>
                  </motion.div>
                  <motion.div 
                    className="bg-gradient-to-br from-venture-50/90 to-venture-100/90 p-4 rounded-md transition-transform hover:scale-105 duration-300 backdrop-blur-sm"
                    whileHover={{ 
                      boxShadow: "0 0 20px rgba(13, 148, 136, 0.4)",
                      y: -5
                    }}
                  >
                    <h3 className="text-venture-700 text-lg font-semibold">15+</h3>
                    <p className="text-sm text-foreground/70">Businesses helped</p>
                  </motion.div>
                  <motion.div 
                    className="bg-gradient-to-br from-booming-50/90 to-booming-100/90 p-4 rounded-md transition-transform hover:scale-105 duration-300 backdrop-blur-sm"
                    whileHover={{ 
                      boxShadow: "0 0 20px rgba(2, 132, 199, 0.4)",
                      y: -5
                    }}
                  >
                    <h3 className="text-booming-700 text-lg font-semibold">Support</h3>
                    <p className="text-sm text-foreground/70">When it is needed</p>
                  </motion.div>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default HeroSection;
