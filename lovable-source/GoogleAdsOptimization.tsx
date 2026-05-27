
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

const GoogleAdsOptimization = () => {
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
                Paid Advertising
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                May 2, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                11 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Google Ads optimization for profitable ROI in 2025
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Stop burning money on Google Ads. Learn the exact optimization strategies that drive profitable ROI and scale winning campaigns consistently.
            </p>
          </motion.header>

          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Google Ads optimization strategies"
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
              Google Ads can be incredibly profitable—or it can drain your budget faster than you imagined. The difference comes down to optimization strategies that most advertisers either don't know or don't implement correctly.
            </p>

            <p className="mb-6">
              In 2025, with increased competition and evolving privacy regulations, successful Google Ads optimization requires a strategic approach that goes beyond basic keyword bidding and ad copy testing.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">The Foundation: Campaign Structure That Scales</h2>
            
            <p className="mb-6">
              Before diving into advanced optimization tactics, you need a campaign structure that can grow with your business. Most advertisers make the mistake of creating overly complex structures that become impossible to manage at scale.
            </p>

            <p className="mb-6">
              The key is to start with a Single Keyword Ad Group (SKAG) approach for your highest-value keywords, then expand systematically. This gives you granular control over bids, ad copy, and landing pages while maintaining manageable campaign structures.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Advanced Bidding Strategies for 2025</h2>
            
            <p className="mb-6">
              Google's automated bidding has evolved significantly, but many advertisers still struggle with implementation. The secret isn't choosing between manual and automated bidding—it's knowing when and how to use each strategy.
            </p>

            <p className="mb-6">
              For new campaigns, start with Enhanced CPC to gather conversion data. Once you have at least 30 conversions in 30 days, transition to Target CPA or Target ROAS. This phased approach gives Google's machine learning the data it needs while protecting your budget during the learning phase.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">The Power of Negative Keywords</h2>
            
            <p className="mb-6">
              Negative keywords are your first line of defense against wasted spend, yet most advertisers treat them as an afterthought. A comprehensive negative keyword strategy can improve your Quality Score, reduce costs, and increase conversion rates simultaneously.
            </p>

            <p className="mb-6">
              Build your negative keyword lists proactively using search term reports, competitor analysis, and industry-specific exclusions. For B2B campaigns, exclude consumer-focused terms early. For local businesses, exclude other geographic areas that don't convert.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Landing Page Optimization That Converts</h2>
            
            <p className="mb-6">
              Your Google Ads optimization efforts are only as strong as your landing pages. In 2025, user experience signals carry more weight than ever in Quality Score calculations and overall campaign performance.
            </p>

            <p className="mb-6">
              Focus on page speed first—aim for loading times under 3 seconds. Then optimize for mobile experience, clear value propositions, and frictionless conversion paths. A/B test headlines, calls-to-action, and form fields to maximize conversion rates.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Audience Targeting in the Privacy Era</h2>
            
            <p className="mb-6">
              With third-party cookies disappearing and privacy regulations tightening, audience targeting requires new approaches. First-party data and Google's own audience signals have become more valuable than ever.
            </p>

            <p className="mb-6">
              Build custom audiences using your customer data, website visitors, and lookalike modeling. Layer demographic and in-market audiences to refine targeting without losing scale. Remember: broader targeting often performs better than overly narrow segments in 2025.
            </p>

            <h2 className="text-3xl font-bold mb-6 mt-12">Performance Monitoring and Optimization</h2>
            
            <p className="mb-6">
              Successful Google Ads optimization requires consistent monitoring and data-driven decision making. Set up automated rules for bid adjustments, pause low-performing keywords, and increase budgets for winning campaigns.
            </p>

            <p className="mb-6">
              Track beyond basic metrics like clicks and impressions. Focus on conversion value, customer lifetime value, and attribution across your entire marketing funnel. This holistic view enables better optimization decisions and proves the true ROI of your Google Ads investment.
            </p>

            <p className="text-lg font-medium mb-8">
              Google Ads optimization in 2025 requires a strategic, data-driven approach combined with continuous testing and refinement. The brands that master these fundamentals while staying agile with new features and targeting options will dominate their markets.
            </p>
          </motion.div>

          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Get More Google Ads Insights</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly Google Ads and PPC optimization tips.
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
            <Link to="/blog/marketing-attribution-modeling-guide" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: Marketing Attribution
            </Link>
            <Link to="/blog/facebook-ads-ios-privacy-guide" className="flex items-center text-booming-600 hover:text-booming-700">
              Next: Facebook Ads iOS Privacy
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default GoogleAdsOptimization;
