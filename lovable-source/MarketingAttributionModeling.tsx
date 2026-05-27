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

const MarketingAttributionModeling = () => {
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
          {/* Back to Blog */}
          <Link to="/blog" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-8">
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back to Blog
          </Link>

          {/* Article Header */}
          <motion.header
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="mb-8"
          >
            <div className="flex items-center gap-4 mb-4">
              <Badge variant="secondary" className="bg-gradient-to-r from-booming-500 to-venture-500 text-white">
                Marketing Analytics
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                April 25, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                13 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Marketing attribution modeling: know what's really driving sales
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Last-click attribution is killing your marketing decisions. Learn advanced attribution modeling to understand the true customer journey and optimize spend.
            </p>
          </motion.header>

          {/* Featured Image */}
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Marketing attribution modeling guide"
              className="w-full h-64 md:h-96 object-cover rounded-lg shadow-lg"
            />
          </motion.div>

          {/* Article Content */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="prose prose-lg max-w-none"
          >
            <p className="text-lg leading-relaxed mb-6">
              If you're still using last-click attribution to make marketing decisions, you're essentially flying blind. Last-click attribution gives 100% of the credit to the final touchpoint before conversion, completely ignoring the nurturing journey that actually convinced your customer to buy.
            </p>

            <p className="mb-6">
              Modern customer journeys are complex, multi-touch experiences that can span days, weeks, or months. Understanding how each touchpoint contributes to conversions isn't just nice to have—it's essential for optimizing your marketing spend and scaling profitably.
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">The Problem with Last-Click Attribution</h2>
            
            <p className="mb-6">
              Here's a typical scenario that last-click attribution gets completely wrong:
            </p>

            <ul className="mb-6">
              <li>Day 1: Customer discovers your brand through a Facebook ad</li>
              <li>Day 3: Returns via Google search to read blog content</li>
              <li>Day 7: Receives email newsletter, clicks to product page</li>
              <li>Day 12: Sees retargeting ad, doesn't click</li>
              <li>Day 15: Googles your brand name, clicks ad, and purchases</li>
            </ul>

            <p className="mb-6">
              Last-click attribution gives 100% credit to the final Google brand search, leading you to believe that brand search is your most valuable channel. In reality, Facebook introduced them to your brand, your content built trust, email nurtured the relationship, and retargeting kept you top-of-mind.
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">Understanding Attribution Models</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">First-Click Attribution</h3>
            
            <p className="mb-6">
              Gives 100% credit to the first touchpoint. Useful for understanding what drives initial awareness, but ignores the nurturing process entirely.
            </p>

            <p className="mb-6">
              <strong>Best for:</strong> Understanding top-of-funnel performance and awareness campaigns.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Linear Attribution</h3>
            
            <p className="mb-6">
              Distributes credit equally across all touchpoints. Simple to understand but doesn't account for the varying importance of different interactions.
            </p>

            <p className="mb-6">
              <strong>Best for:</strong> Getting a baseline understanding of multi-channel journeys.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Time Decay Attribution</h3>
            
            <p className="mb-6">
              Gives more credit to touchpoints closer to conversion. Recognizes that recent interactions are often more influential in driving immediate action.
            </p>

            <p className="mb-6">
              <strong>Best for:</strong> Businesses with shorter sales cycles or impulse purchases.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Position-Based (U-Shaped) Attribution</h3>
            
            <p className="mb-6">
              Gives 40% credit each to first and last touchpoints, distributing the remaining 20% among middle interactions. Recognizes both awareness and conversion moments.
            </p>

            <p className="mb-6">
              <strong>Best for:</strong> Most businesses as it balances awareness and conversion importance.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Data-Driven Attribution</h3>
            
            <p className="mb-6">
              Uses machine learning to analyze your actual data and assign credit based on statistical contribution to conversions. The most accurate but requires significant data volume.
            </p>

            <p className="mb-6">
              <strong>Best for:</strong> Large businesses with substantial conversion data (1000+ conversions monthly).
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">Implementing Attribution Modeling</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">Google Analytics 4 Attribution</h3>
            
            <p className="mb-6">
              GA4 uses data-driven attribution by default but allows you to compare different models:
            </p>

            <ol className="mb-6">
              <li>Navigate to Reports {'->'} Attribution {'->'} Conversion Paths</li>
              <li>Select different attribution models to compare</li>
              <li>Analyze how credit distribution changes between models</li>
              <li>Use insights to adjust channel strategy and budget allocation</li>
            </ol>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Platform-Specific Attribution</h3>
            
            <p className="mb-6">
              <strong>Facebook Attribution:</strong>
            </p>
            <ul className="mb-6">
              <li>1-day view, 7-day click attribution windows</li>
              <li>Cross-device tracking capabilities</li>
              <li>Incrementality testing for true impact measurement</li>
            </ul>

            <p className="mb-6">
              <strong>Google Ads Attribution:</strong>
            </p>
            <ul className="mb-6">
              <li>Data-driven attribution for Search and Shopping campaigns</li>
              <li>Cross-channel attribution insights</li>
              <li>Custom attribution models based on your business goals</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Custom Attribution Solutions</h3>
            
            <p className="mb-6">
              For complex B2B journeys or unique business models, consider building custom attribution:
            </p>

            <ul className="mb-6">
              <li><strong>UTM parameter tracking:</strong> Detailed source/medium/campaign tracking</li>
              <li><strong>CRM integration:</strong> Connect marketing touchpoints to sales outcomes</li>
              <li><strong>Customer surveys:</strong> Ask customers how they discovered you</li>
              <li><strong>Marketing mix modeling:</strong> Statistical analysis of all marketing inputs</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Advanced Attribution Strategies</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">Multi-Touch Attribution for B2B</h3>
            
            <p className="mb-6">
              B2B sales cycles often involve multiple stakeholders and extended evaluation periods. Track these key touchpoints:
            </p>

            <ul className="mb-6">
              <li>Initial awareness (content consumption, ad clicks)</li>
              <li>Consideration phase (product demos, case study downloads)</li>
              <li>Evaluation phase (pricing page visits, competitor comparisons)</li>
              <li>Decision phase (sales conversations, proposal requests)</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Cross-Device Attribution</h3>
            
            <p className="mb-6">
              Modern customers interact across multiple devices. Ensure your attribution model accounts for:
            </p>

            <ul className="mb-6">
              <li>Desktop research, mobile purchase behavior</li>
              <li>App and website interactions</li>
              <li>Email clicks on mobile, conversions on desktop</li>
              <li>Social media discovery, direct website returns</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Offline Attribution</h3>
            
            <p className="mb-6">
              Don't forget offline touchpoints that influence online conversions:
            </p>

            <ul className="mb-6">
              <li>Trade show interactions</li>
              <li>Print advertising exposure</li>
              <li>Word-of-mouth referrals</li>
              <li>Retail store visits</li>
              <li>Call center interactions</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Optimizing Based on Attribution Insights</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">Budget Reallocation</h3>
            
            <p className="mb-6">
              Use attribution insights to make smarter budget decisions:
            </p>

            <ul className="mb-6">
              <li><strong>Assist channels:</strong> Increase investment in channels that assist conversions even if they don't get last-click credit</li>
              <li><strong>Awareness campaigns:</strong> Recognize the value of top-funnel activities that drive initial discovery</li>
              <li><strong>Nurturing touchpoints:</strong> Invest in email, content, and retargeting that guide prospects through the journey</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Creative and Messaging Optimization</h3>
            
            <p className="mb-6">
              Tailor creative based on touchpoint roles:
            </p>

            <ul className="mb-6">
              <li><strong>First touchpoint:</strong> Focus on awareness and brand introduction</li>
              <li><strong>Middle touchpoints:</strong> Emphasize education, benefits, and trust-building</li>
              <li><strong>Final touchpoints:</strong> Create urgency and remove final objections</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Channel Strategy Refinement</h3>
            
            <p className="mb-6">
              Understand each channel's unique role:
            </p>

            <ul className="mb-6">
              <li><strong>Awareness channels:</strong> Social media, display advertising, content marketing</li>
              <li><strong>Consideration channels:</strong> Search, email nurturing, retargeting</li>
              <li><strong>Conversion channels:</strong> Brand search, direct traffic, sales outreach</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Common Attribution Pitfalls</h2>
            
            <ul className="mb-6">
              <li><strong>Attribution window errors:</strong> Using windows that are too short for your sales cycle</li>
              <li><strong>Platform bias:</strong> Only looking at attribution within individual platforms</li>
              <li><strong>Ignoring view-through attribution:</strong> Not accounting for ad impressions that don't result in clicks</li>
              <li><strong>Static model thinking:</strong> Using the same attribution model for all campaign types</li>
              <li><strong>Data silos:</strong> Not connecting marketing data with sales outcomes</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Building Your Attribution Strategy</h2>
            
            <p className="mb-6">
              Start with these steps to implement effective attribution modeling:
            </p>

            <ol className="mb-6">
              <li><strong>Audit current tracking:</strong> Ensure all touchpoints are properly tagged and tracked</li>
              <li><strong>Map customer journeys:</strong> Document typical paths to conversion for different customer segments</li>
              <li><strong>Choose appropriate models:</strong> Select attribution models that match your business complexity and data volume</li>
              <li><strong>Test and validate:</strong> Compare attribution insights with customer interviews and surveys</li>
              <li><strong>Optimize iteratively:</strong> Use insights to make budget and strategy adjustments, then measure results</li>
            </ol>

            <h2 className="text-3xl font-bold mt-12 mb-6">The Future of Attribution</h2>
            
            <p className="mb-6">
              As privacy regulations increase and third-party cookies disappear, attribution is evolving toward:
            </p>

            <ul className="mb-6">
              <li><strong>First-party data reliance:</strong> Building direct relationships with customers</li>
              <li><strong>Predictive modeling:</strong> Using AI to understand influence without perfect tracking</li>
              <li><strong>Incrementality testing:</strong> Measuring true causal impact of marketing activities</li>
              <li><strong>Unified measurement:</strong> Combining online and offline data for complete journey understanding</li>
            </ul>

            <p className="mb-6">
              The businesses that succeed in this new landscape will be those that invest in sophisticated measurement and attribution capabilities now, building the foundation for privacy-compliant, accurate marketing optimization.
            </p>

            <p className="mb-6">
              Remember: Perfect attribution is impossible, but better attribution is always achievable. Start with the tools and models available today, and continuously refine your approach as you gather more data and insights about your customers' true journey to conversion.
            </p>
          </motion.div>

          {/* Newsletter CTA */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Get More Analytics Insights</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly marketing analytics and attribution tips.
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

          {/* Navigation */}
          <div className="flex justify-between items-center mt-12 pt-8 border-t">
            <Link to="/blog/lead-magnet-strategies-high-conversion" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: Lead Magnet Strategies
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

export default MarketingAttributionModeling;
