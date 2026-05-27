
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { 
  CalendarDays, 
  Clock, 
  ArrowLeft, 
  Share2, 
  Info,
  CheckCircle,
  Zap,
  Calculator,
  TrendingUp,
  BarChart3,
  Lightbulb,
  MapPin,
  AlertTriangle,
  DollarSign,
  TrendingDown,
  Clock3,
  Users,
  Target
} from "lucide-react";

const HiddenCostsInefficientFunnels = () => {
  return (
    <div className="min-h-screen">
      <Navbar />
      
      {/* Hero Section */}
      <section className="pt-24 pb-8 bg-gradient-to-br from-booming-50 to-venture-50 relative overflow-hidden">
        <div className="absolute inset-0 opacity-5">
          <div className="absolute top-10 left-10 w-32 h-32 bg-booming-400 rounded-full blur-3xl animate-pulse"></div>
          <div className="absolute bottom-20 right-20 w-48 h-48 bg-venture-400 rounded-full blur-3xl animate-pulse delay-1000"></div>
        </div>
        
        <div className="container mx-auto px-4 md:px-6 relative">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            className="max-w-4xl mx-auto"
          >
            <Link to="/blog" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-6 font-medium">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back to Blog
            </Link>
            
            <div className="flex items-center gap-2 mb-4">
              <MapPin className="h-4 w-4 text-booming-600" />
              <span className="text-booming-600 text-sm font-medium">Rotterdam, Netherlands</span>
            </div>
            
            <Badge className="bg-gradient-to-r from-booming-500 to-venture-500 text-white mb-4">
              Funnel Optimization
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              The hidden costs of inefficient marketing funnels
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Inefficient funnels cost more than just conversions - discover the hidden expenses that are silently draining your marketing budget and how to fix them.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 2, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                8 min read
              </div>
              <Button variant="outline" size="sm">
                <Share2 className="h-4 w-4 mr-2" />
                Share
              </Button>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Featured Image */}
      <section className="pb-8">
        <div className="container mx-auto px-4 md:px-6">
          <div className="max-w-4xl mx-auto">
            <div className="aspect-video rounded-2xl overflow-hidden shadow-2xl">
              <img 
                src="https://images.unsplash.com/photo-1487958449943-2429e8be8625?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="Hidden costs of inefficient marketing funnels and optimization strategies"
                className="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </section>

      {/* Article Content */}
      <section className="pb-16">
        <div className="container mx-auto px-4 md:px-6">
          <div className="max-w-4xl mx-auto">
            <div className="grid lg:grid-cols-4 gap-12">
              {/* Main Content */}
              <div className="lg:col-span-3">
                <article className="prose prose-lg max-w-none">
                  {/* Introduction */}
                  <div className="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <AlertTriangle className="h-6 w-6 text-red-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        Every day, Dutch businesses lose thousands of euros to inefficient marketing funnels. While most companies focus on obvious metrics like conversion rates, the hidden costs—wasted ad spend, increased customer acquisition costs, team time losses, and opportunity costs—often dwarf the visible losses. For Rotterdam businesses competing in 2024's expensive marketing landscape, understanding and eliminating these hidden costs is essential for survival and growth.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Compound Effect of Funnel Leaks</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      A 10% conversion rate might seem acceptable, but what about the 90% of prospects who didn't convert? Each lost prospect represents not just a missed sale, but compounding costs across every stage of your marketing funnel.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Consider this scenario: You spend €50 to acquire a website visitor through ads. If they don't convert, you've lost the €50. But the true cost is higher—you've also lost the time your team spent creating the ad, the opportunity cost of not reaching a more qualified prospect, and the potential lifetime value of that customer, which could be €2,000 or more.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam SaaS company discovered their demo request form was losing 67% of qualified prospects due to too many required fields. This wasn't just costing them conversions—at €150 cost per visitor, they were wasting €100,500 monthly on traffic that hit a broken conversion point. After simplifying the form, their conversion rate increased by 180%, effectively saving them €60,300 in monthly ad spend.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Team Time and Productivity Drains</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Inefficient funnels don't just waste advertising budget—they waste human resources. Sales teams spend time following up on unqualified leads. Customer service handles confused prospects who couldn't find what they needed. Marketing teams create more content to compensate for poor conversion rates.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      When leads aren't properly qualified and nurtured through your funnel, your sales team wastes time on prospects who will never buy. Industry data shows that 67% of sales time is spent on unqualified leads in companies with poor funnel optimization.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch consulting firm calculated that their sales team was spending 40 hours weekly on leads who weren't decision-makers. By implementing better lead qualification in their funnel, they reduced this to 8 hours weekly, effectively gaining 32 hours of productive sales time. At their average deal size, this translated to €45,000 in additional monthly revenue.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">Escalating Customer Acquisition Costs</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      As digital advertising becomes more competitive and expensive, inefficient funnels force you to pay premium prices for the same results. When your funnel converts poorly, you need more traffic to hit your sales targets, driving up your cost per acquisition across all channels.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This creates a dangerous spiral: higher costs require higher prices or lower profit margins, making you less competitive in the market. Meanwhile, competitors with optimized funnels can afford to bid more aggressively for the same keywords and audiences.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam e-commerce business was spending €200 per customer acquisition while competitors achieved €80 CAC in the same market. Analysis revealed their checkout process had seven steps compared to competitors' three-step process. Streamlining their checkout reduced their CAC to €75 and increased monthly revenue by €240,000.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">Opportunity Cost and Market Share Loss</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Perhaps the largest hidden cost is opportunity cost. Every prospect lost to funnel inefficiency is a prospect who might convert with a competitor. In competitive Dutch markets, this translates directly to market share loss and reduced business growth.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Consider the long-term implications: a customer you lose today might have a lifetime value of €5,000 and could have referred three additional customers worth €15,000 total. The true cost of that funnel leak isn't just the immediate lost sale—it's €20,000 in total lost opportunity.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch financial services company discovered they were losing 45% of prospects during their lengthy application process. Competitors with streamlined applications were capturing this market share. By reducing their process from 20 minutes to 6 minutes, they increased conversions by 120% and recaptured significant market share, adding €1.2M in annual revenue.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Target className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        The hidden costs of inefficient funnels compound daily, creating an invisible drain on your business growth. Start by identifying your biggest funnel leaks, calculate the true cost of each inefficiency, and prioritize improvements based on potential impact. Remember: every 1% improvement in conversion rates can translate to thousands of euros in annual savings and additional revenue.
                      </p>
                    </div>
                  </div>
                </article>
              </div>

              {/* Sidebar */}
              <div className="lg:col-span-1">
                <div className="sticky top-24 space-y-6">
                  <Card className="bg-gradient-to-br from-booming-50 to-venture-50 border-booming-200">
                    <CardHeader>
                      <CardTitle className="text-lg flex items-center gap-2">
                        <Zap className="h-5 w-5 text-booming-600" />
                        Free Cost Calculator
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <p className="text-sm text-muted-foreground">
                        Calculate how much funnel leaks are costing you.
                      </p>
                      <div className="space-y-3">
                        <Link to="/funnel-calculator" className="block">
                          <Button className="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700">
                            <Calculator className="h-4 w-4 mr-2" />
                            Funnel Leak Calculator
                          </Button>
                        </Link>
                        <Link to="/roi-forecaster" className="block">
                          <Button className="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700">
                            <TrendingUp className="h-4 w-4 mr-2" />
                            ROI Forecaster
                          </Button>
                        </Link>
                      </div>
                    </CardContent>
                  </Card>

                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg flex items-center gap-2">
                        <BarChart3 className="h-5 w-5" />
                        Article Insights
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Reading time</span>
                        <span className="font-medium">8 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">Funnel Optimization</Badge>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <Footer />
    </div>
  );
};

export default HiddenCostsInefficientFunnels;
