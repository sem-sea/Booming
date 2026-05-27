
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

const AdvancedAnalyticsDataDriven = () => {
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
                Analytics & Data
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                June 12, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                18 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Advanced analytics for data-driven marketing decisions
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Stop guessing and start knowing. Master advanced analytics techniques that reveal hidden insights and drive profitable marketing decisions.
            </p>
          </motion.header>

          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Advanced marketing analytics"
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
              Basic analytics tell you what happened. Advanced analytics tell you why it happened, what will happen next, and how to influence those outcomes. This is the difference between reactive and predictive marketing.
            </p>

            <p className="mb-6">
              In 2025, businesses that master advanced analytics have a competitive advantage that compounds over time. They make better decisions faster, optimize campaigns more effectively, and identify opportunities that others miss entirely.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Beyond Standard Metrics: What Really Matters</h2>
            
            <p className="mb-6">
              Most marketers are drowning in data but starving for insights. They track vanity metrics like page views and social media followers while missing the indicators that actually predict business success.
            </p>

            <p className="mb-6">
              Advanced analytics focuses on leading indicators rather than lagging ones. Customer lifetime value trends, engagement quality scores, and conversion probability models provide actionable insights that standard reports can't deliver.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Cohort Analysis: Understanding Customer Behavior Over Time</h2>
            
            <p className="mb-6">
              Cohort analysis reveals how customer behavior changes over time, helping you identify which acquisition channels produce the most valuable customers and when retention efforts should be intensified.
            </p>

            <p className="mb-6">
              By grouping customers based on when they first engaged with your business, you can track their journey and identify patterns that predict long-term value. This insight is crucial for optimizing marketing spend and customer experience investments.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Attribution Modeling: Giving Credit Where It's Due</h2>
            
            <p className="mb-6">
              First-click and last-click attribution models oversimplify the customer journey. Advanced attribution modeling considers all touchpoints and their relative influence on conversion decisions.
            </p>

            <p className="mb-6">
              Data-driven attribution uses machine learning to analyze actual conversion paths and assign credit based on statistical significance. This approach reveals which marketing channels and campaigns truly drive results, enabling better budget allocation.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Predictive Analytics: Anticipating Future Outcomes</h2>
            
            <p className="mb-6">
              Predictive analytics uses historical data and machine learning algorithms to forecast future customer behavior. This capability transforms marketing from reactive to proactive, allowing you to influence outcomes before they occur.
            </p>

            <p className="mb-6">
              Lead scoring models predict which prospects are most likely to convert, while churn prediction identifies customers at risk of leaving. Customer lifetime value models help prioritize retention efforts and optimize acquisition strategies.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Segmentation: Moving Beyond Demographics</h2>
            
            <p className="mb-6">
              Traditional demographic segmentation provides limited insights in 2025. Behavioral segmentation based on engagement patterns, purchase history, and interaction preferences creates more actionable customer groups.
            </p>

            <p className="mb-6">
              RFM analysis (Recency, Frequency, Monetary) segments customers based on their actual behavior rather than assumed characteristics. This approach enables personalized marketing campaigns that resonate with specific customer needs and preferences.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Real-Time Analytics and Automated Insights</h2>
            
            <p className="mb-6">
              Real-time analytics enables immediate response to changing conditions and emerging opportunities. Automated alerts notify you when key metrics deviate from expected ranges, allowing rapid optimization adjustments.
            </p>

            <p className="mb-6">
              Machine learning algorithms can identify anomalies, trends, and opportunities faster than human analysts. This capability is essential for maintaining competitive advantage in fast-moving markets.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Building Your Advanced Analytics Stack</h2>
            
            <p className="mb-6">
              Creating an advanced analytics capability requires the right tools, processes, and skills. Start with data consolidation—bringing all your marketing data into a single, unified view.
            </p>

            <p className="mb-6">
              Focus on data quality before adding complexity. Clean, consistent data is more valuable than sophisticated analysis of flawed information. Invest in data governance and validation processes early in your analytics journey.
            </p>

            <p className="text-lg font-medium mb-8">
              Advanced analytics isn't just about having better reports—it's about making better decisions. The organizations that embrace data-driven marketing in 2025 will outperform their competitors by making smarter, faster decisions based on deeper insights.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Master Data-Driven Marketing</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly analytics and data insights.
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
            <Link to="/blog/ecommerce-cro-abandoned-cart-recovery" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: Ecommerce CRO
            </Link>
            <Link to="/blog" className="flex items-center text-booming-600 hover:text-booming-700">
              Back to Blog
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default AdvancedAnalyticsDataDriven;
