
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

const EcommerceCroAbandonedCart = () => {
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
                Ecommerce CRO
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                June 5, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                12 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Ecommerce CRO: abandoned cart recovery that converts 35%+
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Cart abandonment is costing you millions. Discover advanced recovery strategies that turn abandoned carts into completed purchases consistently.
            </p>
          </motion.header>

          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="/lovable-uploads/0100bc15-cf04-42bc-827d-1ecfe72c1ff6.png"
              alt="Ecommerce abandoned cart recovery strategy planning session"
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
              The average cart abandonment rate is 70%, representing massive lost revenue. But with the right recovery strategies, you can convert 35%+ of abandoned carts into completed purchases.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Boost Your Ecommerce Conversions</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly ecommerce optimization tips.
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
            <Link to="/blog/seo-content-marketing-integration" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: SEO Content Marketing
            </Link>
            <Link to="/blog/advanced-analytics-data-driven-decisions" className="flex items-center text-booming-600 hover:text-booming-700">
              Next: Advanced Analytics
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default EcommerceCroAbandonedCart;
