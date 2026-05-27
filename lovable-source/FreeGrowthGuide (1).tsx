
import { useState } from "react";
import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Download, CheckCircle, TrendingUp, Target, Zap, ArrowLeft } from "lucide-react";
import { toast } from "@/hooks/use-toast";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { addContactToBrevo } from "@/services/brevoService";
import { downloadGrowthGuidePDF } from "@/components/growth-guide/GrowthGuidePDF";

const FreeGrowthGuidePage = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need your email to provide you with the growth strategy guide",
        variant: "destructive",
      });
      return;
    }

    setIsSubmitting(true);
    
    try {
      const success = await addContactToBrevo({
        email,
        attributes: {
          LEAD_SOURCE: "Free Growth Guide Page"
        },
        listIds: [2] // Free Strategy Guide list
      });

      if (success) {
        // Generate and download PDF immediately
        downloadGrowthGuidePDF();
        
        toast({
          title: "Thank you!",
          description: "Your Free Growth Strategy Guide is now downloading. Check your downloads folder.",
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
    <div className="min-h-screen flex flex-col">
      <Navbar />
      
      <main className="flex-grow pt-24">
        {/* Hero Section */}
        <section className="py-16 md:py-24 bg-gradient-to-br from-booming-50 to-venture-50">
          <div className="container mx-auto px-4 md:px-6">
            <div className="max-w-4xl mx-auto text-center">
              <Link to="/" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-8">
                <ArrowLeft className="mr-2 h-4 w-4" />
                Back to Home
              </Link>
              
              <motion.h1 
                className="text-4xl md:text-6xl font-bold mb-6"
                initial={{ opacity: 0, y: 30 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.7 }}
              >
                Free Growth Strategy Guide
              </motion.h1>
              
              <motion.p 
                className="text-xl text-foreground/70 mb-8 max-w-2xl mx-auto"
                initial={{ opacity: 0, y: 40 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2, duration: 0.7 }}
              >
                Learn the exact strategies our clients use to achieve 48%+ growth. 
                This comprehensive guide includes actionable frameworks, templates, and real case studies.
              </motion.p>

              <motion.div 
                className="bg-white/80 backdrop-blur-sm p-8 rounded-xl shadow-lg max-w-md mx-auto"
                initial={{ opacity: 0, y: 50 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.4, duration: 0.7 }}
              >
                <form onSubmit={handleSubmit} className="space-y-4">
                  <Input
                    type="email"
                    placeholder="Enter your business email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="text-center"
                    required
                  />
                  <Button 
                    type="submit" 
                    disabled={isSubmitting}
                    className="w-full bg-booming-600 hover:bg-booming-700 text-white py-6 text-lg"
                  >
                    {isSubmitting ? "Preparing..." : "Download Now"}
                    <Download className="ml-2 h-5 w-5" />
                  </Button>
                </form>
              </motion.div>
            </div>
          </div>
        </section>

        {/* What's Inside Section */}
        <section className="py-16 md:py-24">
          <div className="container mx-auto px-4 md:px-6">
            <div className="max-w-4xl mx-auto">
              <h2 className="text-3xl md:text-4xl font-bold text-center mb-12">
                What's Inside the Guide
              </h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.1, duration: 0.6 }}
                >
                  <div className="bg-booming-100 p-3 rounded-full w-fit mb-4">
                    <TrendingUp className="h-6 w-6 text-booming-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">Actionable growth strategies</h3>
                  <p className="text-foreground/70">Proven methodologies and frameworks that deliver consistent results for businesses of all sizes.</p>
                </motion.div>

                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.2, duration: 0.6 }}
                >
                  <div className="bg-venture-100 p-3 rounded-full w-fit mb-4">
                    <Zap className="h-6 w-6 text-venture-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">AI implementation guide</h3>
                  <p className="text-foreground/70">Step-by-step instructions for integrating AI tools into your marketing workflow effectively.</p>
                </motion.div>

                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.3, duration: 0.6 }}
                >
                  <div className="bg-booming-100 p-3 rounded-full w-fit mb-4">
                    <Target className="h-6 w-6 text-booming-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">ROI calculation templates</h3>
                  <p className="text-foreground/70">Ready-to-use spreadsheets and formulas to track and optimize your marketing ROI.</p>
                </motion.div>

                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.4, duration: 0.6 }}
                >
                  <div className="bg-venture-100 p-3 rounded-full w-fit mb-4">
                    <CheckCircle className="h-6 w-6 text-venture-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">Real case studies</h3>
                  <p className="text-foreground/70">Detailed breakdowns of successful campaigns with actual numbers and insights.</p>
                </motion.div>

                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.5, duration: 0.6 }}
                >
                  <div className="bg-booming-100 p-3 rounded-full w-fit mb-4">
                    <TrendingUp className="h-6 w-6 text-booming-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">Growth frameworks</h3>
                  <p className="text-foreground/70">Tested frameworks for sustainable growth that you can implement immediately.</p>
                </motion.div>

                <motion.div 
                  className="bg-white p-6 rounded-lg shadow-sm border"
                  initial={{ opacity: 0, y: 30 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.6, duration: 0.6 }}
                >
                  <div className="bg-venture-100 p-3 rounded-full w-fit mb-4">
                    <Target className="h-6 w-6 text-venture-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">Implementation roadmap</h3>
                  <p className="text-foreground/70">A clear 90-day plan to implement these strategies in your business.</p>
                </motion.div>
              </div>
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-16 md:py-24 bg-gradient-to-r from-booming-600 to-venture-600">
          <div className="container mx-auto px-4 md:px-6">
            <div className="max-w-2xl mx-auto text-center text-white">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">
                Ready to accelerate your growth?
              </h2>
              <p className="text-xl mb-8 opacity-90">
                Download your free guide now and start implementing these strategies today.
              </p>
              <div className="bg-white/10 backdrop-blur-sm p-6 rounded-xl">
                <form onSubmit={handleSubmit} className="flex flex-col sm:flex-row gap-4">
                  <Input
                    type="email"
                    placeholder="Enter your business email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="flex-grow bg-white text-black"
                    required
                  />
                  <Button 
                    type="submit" 
                    disabled={isSubmitting}
                    className="bg-white text-booming-600 hover:bg-gray-100 px-8"
                  >
                    {isSubmitting ? "Preparing..." : "Get Free Guide"}
                    <Download className="ml-2 h-4 w-4" />
                  </Button>
                </form>
                <p className="text-sm opacity-70 mt-3">
                  Your guide will download immediately. No spam, ever.
                </p>
              </div>
            </div>
          </div>
        </section>
      </main>
      
      <Footer />
    </div>
  );
};

export default FreeGrowthGuidePage;
