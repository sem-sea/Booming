
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

const ConversionRateOptimizationAudit = () => {
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
                Conversion Optimization
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                April 3, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                15 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Complete CRO audit checklist: find hidden conversion killers
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Most websites have invisible conversion barriers killing 30-50% of potential sales. Use this comprehensive audit checklist to uncover and fix what's costing you money.
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
              alt="Complete CRO audit checklist"
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
              Your website might be leaking money, and you don't even know it. Studies show that most websites lose 30-50% of potential conversions due to hidden barriers and optimization oversights. The worst part? These issues are often invisible to business owners who are too close to their own product.
            </p>

            <p className="mb-6">
              This comprehensive conversion rate optimization (CRO) audit checklist will help you systematically identify and eliminate the conversion killers hiding in your website. Every item on this list has been proven to impact conversion rates significantly.
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">The Hidden Cost of Poor Conversion Rates</h2>
            
            <p className="mb-6">
              Before diving into the checklist, let's understand what's at stake. If your website converts at 2% instead of 4%, you're literally throwing away half your potential revenue. For a business driving 10,000 visitors monthly with an average order value of €100, that's €20,000 lost every month.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Technical Performance Audit</h3>
            
            <p className="mb-4">
              <strong>Page Speed Analysis:</strong>
            </p>
            <ul className="mb-6">
              <li>Core Web Vitals scores (LCP, FID, CLS)</li>
              <li>Mobile page speed (should be under 3 seconds)</li>
              <li>Time to First Byte (TTFB)</li>
              <li>Image optimization and lazy loading</li>
              <li>Critical CSS rendering</li>
            </ul>

            <p className="mb-4">
              <strong>Mobile Responsiveness:</strong>
            </p>
            <ul className="mb-6">
              <li>Touch-friendly button sizes (minimum 44px)</li>
              <li>Readable text without zooming</li>
              <li>Horizontal scrolling issues</li>
              <li>Mobile-specific navigation</li>
              <li>Form usability on mobile devices</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">User Experience (UX) Audit</h3>
            
            <p className="mb-4">
              <strong>Navigation and Information Architecture:</strong>
            </p>
            <ul className="mb-6">
              <li>Clear value proposition within 5 seconds</li>
              <li>Intuitive navigation structure</li>
              <li>Search functionality (if applicable)</li>
              <li>Breadcrumb navigation</li>
              <li>404 error page optimization</li>
            </ul>

            <p className="mb-4">
              <strong>Trust and Credibility Signals:</strong>
            </p>
            <ul className="mb-6">
              <li>Customer testimonials and reviews</li>
              <li>Security badges and certifications</li>
              <li>Contact information visibility</li>
              <li>About page completeness</li>
              <li>Professional design quality</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Conversion Funnel Analysis</h3>
            
            <p className="mb-4">
              <strong>Landing Page Optimization:</strong>
            </p>
            <ul className="mb-6">
              <li>Message-match with traffic source</li>
              <li>Clear and compelling headlines</li>
              <li>Benefit-focused copy</li>
              <li>Strong call-to-action buttons</li>
              <li>Minimal distractions above the fold</li>
            </ul>

            <p className="mb-4">
              <strong>Form Optimization:</strong>
            </p>
            <ul className="mb-6">
              <li>Number of form fields (fewer is better)</li>
              <li>Clear field labels and placeholders</li>
              <li>Error message clarity</li>
              <li>Progress indicators for multi-step forms</li>
              <li>Smart field defaults and auto-fill</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Psychological Conversion Triggers</h3>
            
            <p className="mb-4">
              <strong>Urgency and Scarcity:</strong>
            </p>
            <ul className="mb-6">
              <li>Limited-time offers</li>
              <li>Stock level indicators</li>
              <li>Countdown timers (when authentic)</li>
              <li>Social proof notifications</li>
              <li>Recent customer activity displays</li>
            </ul>

            <p className="mb-4">
              <strong>Risk Reduction:</strong>
            </p>
            <ul className="mb-6">
              <li>Money-back guarantees</li>
              <li>Free trial offerings</li>
              <li>Clear return/refund policies</li>
              <li>Customer service accessibility</li>
              <li>FAQ sections addressing objections</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Advanced Analytics Review</h3>
            
            <p className="mb-4">
              <strong>Behavioral Analytics:</strong>
            </p>
            <ul className="mb-6">
              <li>Heat map analysis of key pages</li>
              <li>User session recordings review</li>
              <li>Scroll depth tracking</li>
              <li>Click tracking on CTAs</li>
              <li>Exit intent behavior patterns</li>
            </ul>

            <p className="mb-4">
              <strong>Conversion Tracking:</strong>
            </p>
            <ul className="mb-6">
              <li>Goal setup and tracking accuracy</li>
              <li>Multi-channel attribution analysis</li>
              <li>Conversion path analysis</li>
              <li>Micro-conversion tracking</li>
              <li>Customer lifetime value tracking</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Implementation Priority Matrix</h2>
            
            <p className="mb-6">
              Not all optimization opportunities are created equal. Use this priority matrix to focus on changes that will deliver the biggest impact:
            </p>

            <p className="mb-4">
              <strong>High Impact, Low Effort (Do First):</strong>
            </p>
            <ul className="mb-6">
              <li>Improve headline clarity</li>
              <li>Optimize CTA button text and colors</li>
              <li>Add trust signals</li>
              <li>Reduce form fields</li>
              <li>Fix mobile usability issues</li>
            </ul>

            <p className="mb-4">
              <strong>High Impact, High Effort (Plan Carefully):</strong>
            </p>
            <ul className="mb-6">
              <li>Complete site redesign</li>
              <li>Implement personalization</li>
              <li>Advanced tracking setup</li>
              <li>A/B testing infrastructure</li>
              <li>Customer journey mapping</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Measuring Success</h2>
            
            <p className="mb-6">
              After implementing changes, track these key metrics to measure improvement:
            </p>

            <ul className="mb-6">
              <li><strong>Overall conversion rate:</strong> Your primary success metric</li>
              <li><strong>Page-specific conversion rates:</strong> Identify which changes work best</li>
              <li><strong>Average order value:</strong> Quality improvements alongside quantity</li>
              <li><strong>Time to conversion:</strong> How quickly visitors convert</li>
              <li><strong>Customer lifetime value:</strong> Long-term impact of optimizations</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Common CRO Mistakes to Avoid</h2>
            
            <p className="mb-6">
              Even with the best intentions, many businesses make these critical mistakes:
            </p>

            <ul className="mb-6">
              <li><strong>Testing too many elements at once:</strong> Makes it impossible to identify what worked</li>
              <li><strong>Stopping tests too early:</strong> Statistical significance takes time</li>
              <li><strong>Ignoring mobile experience:</strong> Over 50% of traffic is mobile</li>
              <li><strong>Focusing only on homepage:</strong> Optimize your entire funnel</li>
              <li><strong>Not considering customer lifetime value:</strong> Sometimes lower immediate conversion rates lead to higher lifetime value</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Getting Started with Your CRO Audit</h2>
            
            <p className="mb-6">
              Start with the technical performance audit - there's no point optimizing psychology if your site doesn't load properly. Then move through UX, conversion funnel, and psychological triggers in that order.
            </p>

            <p className="mb-6">
              Remember: CRO is not a one-time project but an ongoing process. Plan to audit your site quarterly and continuously test improvements based on user behavior and business goals.
            </p>

            <p className="mb-6">
              The businesses that succeed with conversion optimization are those that approach it systematically, measure everything, and remain committed to continuous improvement. Your website is your most important sales asset - invest in optimizing it accordingly.
            </p>
          </motion.div>

          {/* Newsletter CTA */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Get More CRO Insights</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly conversion optimization tips and strategies.
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
            <Link to="/blog/video-marketing-dominance-2025" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: Video Marketing Dominance
            </Link>
            <Link to="/blog/lead-magnet-strategies-high-conversion" className="flex items-center text-booming-600 hover:text-booming-700">
              Next: Lead Magnet Strategies
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default ConversionRateOptimizationAudit;
