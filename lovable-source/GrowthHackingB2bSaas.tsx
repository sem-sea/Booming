
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, ArrowRight } from "lucide-react";
import { useState } from "react";
import { toast } from "@/hooks/use-toast";
import { addContactToBrevo } from "@/services/brevoService";
import { checkForSpam } from "@/utils/spamFilter";

const GrowthHackingB2bSaas = () => {
  const [email, setEmail] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleNewsletterSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
      toast({
        title: "Please enter a valid email",
        description: "We need a valid email to subscribe you to our newsletter",
        variant: "destructive",
      });
      return;
    }

    const spamCheck = checkForSpam(email);
    
    if (spamCheck.isSpam) {
      console.log('Spam detected in blog newsletter:', spamCheck);
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
          LEAD_SOURCE: "Newsletter Blog Article"
        },
        listIds: [3]
      });

      if (success) {
        toast({
          title: "Welcome aboard! 🚀",
          description: "You've joined 700+ members getting growth insights.",
          variant: "default",
        });
        setEmail("");
      } else {
        throw new Error("Failed to add contact");
      }
    } catch (error) {
      console.error("Error submitting newsletter:", error);
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
    <div className="min-h-screen">
      <Navbar />
      
      <article className="pt-24 pb-16">
        <div className="container mx-auto px-4 md:px-6 max-w-4xl">
          <Link to="/blog" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-8">
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Blog
          </Link>

          <motion.header
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="mb-8"
          >
            <div className="flex items-center gap-4 mb-4">
              <Badge variant="secondary" className="bg-gradient-to-r from-booming-500 to-venture-500 text-white">
                Growth Strategy
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                May 15, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                16 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Growth hacking for B2B SaaS: proven strategies that scale
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Traditional marketing isn't enough for B2B SaaS growth. Discover unconventional growth hacking strategies that drive viral adoption and sustainable scaling.
            </p>
          </motion.header>

          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="/lovable-uploads/d19b0557-feac-429a-b591-46df50cce937.png"
              alt="B2B SaaS growth hacking presentation in modern office environment"
              className="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg"
            />
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="prose prose-lg max-w-none"
          >
            <p className="text-lg leading-relaxed mb-6">
              B2B SaaS companies that achieve exponential growth don't just rely on traditional marketing. They implement creative growth hacking strategies that turn users into evangelists and create viral adoption loops.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Get More Growth Hacking Insights</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly B2B SaaS growth strategies.
            </p>
            <form onSubmit={handleNewsletterSubmit} className="flex gap-2 max-w-md mx-auto">
              <Input 
                type="email"
                placeholder="Enter your email" 
                className="bg-white" 
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
              <Button 
                type="submit"
                disabled={isSubmitting}
                className="bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700 shrink-0"
              >
                {isSubmitting ? "..." : "Subscribe"}
                {!isSubmitting && <ArrowRight className="ml-2 h-4 w-4" />}
              </Button>
            </form>
          </motion.div>

          <div className="flex justify-between items-center mt-12 pt-8 border-t">
            <Link to="/blog/facebook-ads-ios-privacy-guide" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: Facebook Ads iOS Privacy
            </Link>
            <Link to="/blog/linkedin-ads-b2b-lead-generation" className="flex items-center text-booming-600 hover:text-booming-700">
              Next: LinkedIn Ads Lead Generation
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default GrowthHackingB2bSaas;
