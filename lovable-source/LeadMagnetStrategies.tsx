
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

const LeadMagnetStrategies = () => {
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
                Lead Generation
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                April 5, 2025
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                12 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 bg-gradient-to-r from-booming-600 to-venture-600 bg-clip-text text-transparent">
              Lead magnet strategies that convert 40%+ of visitors
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Generic ebooks are dead. Discover the high-converting lead magnets that turn cold traffic into qualified prospects and paying customers.
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
              src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Lead magnet strategies that convert"
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
              The era of generic "Top 10 Tips" ebooks is over. Modern consumers are bombarded with so much content that they've become incredibly selective about what they'll exchange their email address for. Yet some lead magnets consistently convert 40%, 50%, even 60% of visitors.
            </p>

            <p className="mb-6">
              What separates high-converting lead magnets from the rest? It's not about design or distribution—it's about understanding the psychology of value exchange and delivering something genuinely useful that solves an immediate problem.
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">The Psychology of High-Converting Lead Magnets</h2>
            
            <p className="mb-6">
              Before diving into specific strategies, let's understand what makes people willing to share their contact information. Three psychological triggers drive most conversions:
            </p>

            <ul className="mb-6">
              <li><strong>Immediate Value:</strong> The perceived benefit must be instant and tangible</li>
              <li><strong>Problem-Solution Fit:</strong> It must address a specific pain point they're experiencing right now</li>
              <li><strong>Low Perceived Risk:</strong> The exchange must feel fair and trustworthy</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Strategy 1: The Ultra-Specific Solution</h3>
            
            <p className="mb-6">
              Instead of broad topics, focus on hyper-specific problems your ideal customers face. For example:
            </p>

            <p className="mb-4">
              <strong>Generic:</strong> "Email Marketing Guide"
            </p>
            <p className="mb-4">
              <strong>Ultra-Specific:</strong> "7-Email Sequence That Converts Abandoned Cart Visitors Into Buyers (With Copy-Paste Templates)"
            </p>

            <p className="mb-6">
              The ultra-specific version works because it addresses a precise problem (abandoned carts) with a clear solution (7-email sequence) and promises immediate usability (copy-paste templates).
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Strategy 2: The Interactive Assessment</h3>
            
            <p className="mb-6">
              People love learning about themselves. Interactive assessments that provide personalized insights can achieve conversion rates of 40-60%. Examples include:
            </p>

            <ul className="mb-6">
              <li>"Marketing Maturity Assessment: Where Does Your Business Stand?"</li>
              <li>"Conversion Rate Audit: Find Your Biggest Leaks"</li>
              <li>"Growth Readiness Score: Are You Ready to Scale?"</li>
            </ul>

            <p className="mb-6">
              The key is providing genuinely valuable, personalized feedback based on their responses, not just generic advice.
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Strategy 3: The "Behind the Scenes" Reveal</h3>
            
            <p className="mb-6">
              Share the exact processes, templates, or strategies you use internally. This works exceptionally well because it satisfies curiosity and provides proven systems:
            </p>

            <ul className="mb-6">
              <li>"The Exact 30-Day Marketing Calendar We Use to Generate €500K+ Monthly"</li>
              <li>"Our Internal Conversion Optimization Checklist (42 Points)"</li>
              <li>"The Sales Script That Closes 67% of Our Qualified Leads"</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Strategy 4: The Timely Solution</h3>
            
            <p className="mb-6">
              Create lead magnets that address current events, seasonal needs, or trending topics in your industry:
            </p>

            <ul className="mb-6">
              <li>"2025 Marketing Budget Planner: Allocate Your Spend for Maximum ROI"</li>
              <li>"GDPR-Compliant Email Marketing: Complete Compliance Checklist"</li>
              <li>"Post-iOS Update: Facebook Ads Optimization Guide"</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Strategy 5: The Quick Win Generator</h3>
            
            <p className="mb-6">
              Offer something that can be implemented immediately with visible results. These work because they demonstrate your expertise while providing instant gratification:
            </p>

            <ul className="mb-6">
              <li>"5-Minute Website Speed Optimization Checklist"</li>
              <li>"Copy-Paste Email Templates That Increase Open Rates by 40%"</li>
              <li>"10 Google Ads Optimizations You Can Do Today"</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Format Innovation for Higher Conversions</h2>
            
            <p className="mb-6">
              The format of your lead magnet is almost as important as the content. Here are high-converting formats beyond traditional PDFs:
            </p>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Video Training Series</h3>
            
            <p className="mb-6">
              Short, focused video lessons (3-7 minutes each) delivered over 3-5 days. This format works because:
            </p>

            <ul className="mb-6">
              <li>Higher perceived value than written content</li>
              <li>Creates anticipation for the next lesson</li>
              <li>Builds a stronger relationship through face-to-face interaction</li>
              <li>Higher engagement rates</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Interactive Tools and Calculators</h3>
            
            <p className="mb-6">
              Custom tools that provide immediate value while capturing leads:
            </p>

            <ul className="mb-6">
              <li>ROI calculators</li>
              <li>Pricing tools</li>
              <li>Audit generators</li>
              <li>Planning templates with auto-calculations</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Email Courses</h3>
            
            <p className="mb-6">
              5-7 day email courses that teach a complete skill or process. Each email should build on the previous one, creating momentum and engagement.
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">Optimization Tactics for Maximum Conversion</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">Title Optimization</h3>
            
            <p className="mb-6">
              Your lead magnet title is often the make-or-break factor. Use these proven formulas:
            </p>

            <ul className="mb-6">
              <li><strong>Number + Benefit + Timeframe:</strong> "5 Landing Page Changes That Increase Conversions in 24 Hours"</li>
              <li><strong>How + Specific Outcome + Without Common Problem:</strong> "How to Double Your Email List Without Spending on Ads"</li>
              <li><strong>Secret/Insider + Authority:</strong> "The Secret Facebook Ads Strategy Used by 7-Figure Ecommerce Brands"</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Landing Page Elements</h3>
            
            <p className="mb-6">
              Your lead magnet landing page should include:
            </p>

            <ul className="mb-6">
              <li><strong>Clear value proposition</strong> in the headline</li>
              <li><strong>Specific benefits</strong> (not features) in bullet points</li>
              <li><strong>Social proof</strong> or credibility indicators</li>
              <li><strong>Preview or sample</strong> of the content</li>
              <li><strong>Minimal form fields</strong> (name and email only)</li>
              <li><strong>Strong call-to-action</strong> focused on the benefit</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">Targeting and Timing</h3>
            
            <p className="mb-6">
              <strong>Exit Intent:</strong> Trigger lead magnets when visitors are about to leave
            </p>
            <p className="mb-6">
              <strong>Scroll-based:</strong> Show after 70% page scroll on relevant content
            </p>
            <p className="mb-6">
              <strong>Time-based:</strong> After 30-60 seconds on page
            </p>
            <p className="mb-6">
              <strong>Content upgrade:</strong> Relevant to specific blog posts or pages
            </p>

            <h2 className="text-3xl font-bold mt-12 mb-6">Measuring and Improving Performance</h2>
            
            <p className="mb-6">
              Track these key metrics to optimize your lead magnets:
            </p>

            <ul className="mb-6">
              <li><strong>Conversion rate:</strong> Visitors to subscribers</li>
              <li><strong>Quality score:</strong> How many leads become customers</li>
              <li><strong>Engagement rate:</strong> How much they interact with the content</li>
              <li><strong>Time to conversion:</strong> How quickly subscribers become customers</li>
              <li><strong>Cost per lead:</strong> If using paid promotion</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">A/B Testing Priorities</h3>
            
            <p className="mb-6">
              Test these elements in order of impact:
            </p>

            <ol className="mb-6">
              <li>Headlines and titles</li>
              <li>Value proposition clarity</li>
              <li>Form length and fields</li>
              <li>Call-to-action button text</li>
              <li>Social proof elements</li>
              <li>Visual design and layout</li>
            </ol>

            <h2 className="text-3xl font-bold mt-12 mb-6">Industry-Specific Lead Magnet Ideas</h2>
            
            <h3 className="text-2xl font-semibold mt-8 mb-4">B2B Services</h3>
            <ul className="mb-6">
              <li>Industry benchmarking reports</li>
              <li>ROI calculators</li>
              <li>Process optimization checklists</li>
              <li>Vendor comparison guides</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">E-commerce</h3>
            <ul className="mb-6">
              <li>Product selection guides</li>
              <li>Size or style quizzes</li>
              <li>Exclusive discount codes</li>
              <li>Care and maintenance guides</li>
            </ul>

            <h3 className="text-2xl font-semibold mt-8 mb-4">SaaS</h3>
            <ul className="mb-6">
              <li>Free trial extensions</li>
              <li>Advanced feature tutorials</li>
              <li>Integration guides</li>
              <li>Best practices from successful customers</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Common Mistakes That Kill Conversions</h2>
            
            <ul className="mb-6">
              <li><strong>Being too broad:</strong> "Marketing Tips" vs. "Instagram Story Templates for Restaurants"</li>
              <li><strong>Poor mobile experience:</strong> Forms that don't work on mobile devices</li>
              <li><strong>Weak follow-up:</strong> Not nurturing leads after they download</li>
              <li><strong>Irrelevant traffic:</strong> Promoting to audiences who don't match your ideal customer</li>
              <li><strong>No clear next step:</strong> Failing to guide leads toward a purchase decision</li>
            </ul>

            <h2 className="text-3xl font-bold mt-12 mb-6">Building Your Lead Magnet Strategy</h2>
            
            <p className="mb-6">
              Start with one high-quality lead magnet that addresses your ideal customer's most pressing problem. Focus on making it genuinely valuable—something they'd be willing to pay for.
            </p>

            <p className="mb-6">
              Once you have a converting lead magnet, create variations for different segments of your audience or different stages of the buyer's journey. The goal is to capture leads at every touchpoint while providing genuine value.
            </p>

            <p className="mb-6">
              Remember: A great lead magnet doesn't just capture email addresses—it demonstrates your expertise, builds trust, and positions you as the obvious choice when they're ready to buy.
            </p>
          </motion.div>

          {/* Newsletter CTA */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16 p-8 bg-gradient-to-r from-booming-50 to-venture-50 rounded-lg"
          >
            <h3 className="text-2xl font-bold mb-4 text-center">Get More Lead Generation Insights</h3>
            <p className="text-center text-muted-foreground mb-6">
              Join 700+ Dutch business leaders getting weekly lead generation and conversion tips.
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
            <Link to="/blog/conversion-rate-optimization-audit-checklist" className="flex items-center text-booming-600 hover:text-booming-700">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Previous: CRO Audit Checklist
            </Link>
            <Link to="/blog/marketing-attribution-modeling-guide" className="flex items-center text-booming-600 hover:text-booming-700">
              Next: Marketing Attribution
              <ArrowRight className="h-4 w-4 ml-2" />
            </Link>
          </div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default LeadMagnetStrategies;
