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
  Fuel,
  Users,
  ArrowDown,
  Target,
  Heart,
  ShoppingCart
} from "lucide-react";

const MarketingFunnelHowItWorks = () => {
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
              Funnel Strategy
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              What is a marketing funnel and how does it actually work today?
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Modern marketing funnels have evolved beyond the traditional AIDA model - understand how today's customer journeys really work and how to optimize them.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 4, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                10 min read
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
                src="https://images.unsplash.com/photo-1460574283810-2aab119d8511?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="Modern marketing funnel strategies and customer journey optimization"
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
                  <div className="bg-gradient-to-r from-purple-50 to-indigo-50 border-l-4 border-purple-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Fuel className="h-6 w-6 text-purple-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        The traditional marketing funnel—Awareness, Interest, Desire, Action—was designed for a simpler world. Today's Dutch consumers don't follow linear paths to purchase. They research on mobile, compare prices across platforms, read reviews, and make decisions across multiple touchpoints. Understanding modern customer journeys is essential for Rotterdam businesses competing in 2024's complex marketplace.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Evolution from Linear to Dynamic Funnels</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Traditional funnels assumed customers moved in a straight line: they became aware of your product, developed interest, desired it, and took action. This worked when customers had limited information sources and fewer options.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Modern customer journeys are dynamic and non-linear. A prospect might discover your brand on Instagram, research on Google, compare prices on a comparison site, read reviews on Trustpilot, visit your website multiple times, abandon their cart, receive retargeting ads, and finally purchase weeks later through a different channel entirely.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam furniture retailer analyzed their customer data and discovered that successful purchasers touched their brand an average of 12 times across 6 different channels before buying. The highest-value customers actually took longer to convert but spent 240% more than quick decision-makers.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Modern Customer Journey: Messy but Measurable</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Today's funnels are better described as customer journey maps with multiple entry points, exit points, and circular patterns. Customers might enter at any stage, loop back to earlier stages, or skip stages entirely based on their prior knowledge and trust level.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      The key is mapping these complex journeys and identifying the critical moments that influence purchase decisions. Advanced analytics can reveal which touchpoints have the highest impact on conversion and which combinations of interactions lead to the best customer lifetime value.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch B2B software company mapped their customer journey and discovered that prospects who downloaded their ROI calculator were 450% more likely to request a demo. However, those who also attended a webinar before downloading had a 78% close rate compared to 23% for calculator-only leads.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Four Stages of Modern Marketing Funnels</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      While customer journeys are non-linear, successful modern funnels still operate around four core stages: Attention, Engagement, Conversion, and Advocacy. The difference is that customers can enter and re-enter these stages multiple times.
                    </p>

                    <div className="bg-gray-50 p-6 rounded-lg mb-6">
                      <h3 className="text-xl font-semibold mb-4 flex items-center gap-2">
                        <Users className="h-5 w-5" />
                        Stage 1: Attention
                      </h3>
                      <p className="mb-4">Getting noticed in a crowded marketplace. This includes SEO, social media, advertising, content marketing, and word-of-mouth referrals.</p>
                      
                      <h3 className="text-xl font-semibold mb-4 flex items-center gap-2">
                        <Heart className="h-5 w-5" />
                        Stage 2: Engagement
                      </h3>
                      <p className="mb-4">Building relationships and trust through valuable content, personalized experiences, and consistent communication across touchpoints.</p>
                      
                      <h3 className="text-xl font-semibold mb-4 flex items-center gap-2">
                        <ShoppingCart className="h-5 w-5" />
                        Stage 3: Conversion
                      </h3>
                      <p className="mb-4">Removing friction from the purchase process and providing compelling reasons to buy now rather than later.</p>
                      
                      <h3 className="text-xl font-semibold mb-4 flex items-center gap-2">
                        <Target className="h-5 w-5" />
                        Stage 4: Advocacy
                      </h3>
                      <p>Turning customers into advocates who refer others and increase their own lifetime value through repeat purchases and upsells.</p>
                    </div>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam restaurant implemented this four-stage approach: Instagram for attention, email newsletter for engagement, seamless online ordering for conversion, and a loyalty program for advocacy. Their customer lifetime value increased by 180% in eight months.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">Optimizing for Multi-Touch Attribution</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Traditional last-click attribution gives all credit to the final touchpoint before conversion. This leads to under-investing in awareness and engagement activities that lay the foundation for eventual purchases.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Multi-touch attribution models distribute credit across all touchpoints that influenced the conversion. This reveals the true value of each marketing channel and helps optimize budget allocation for maximum ROI.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch e-commerce business switched from last-click to multi-touch attribution and discovered their blog content was influencing 67% of conversions, even though it rarely got final-click credit. By investing more in content marketing and less in remarketing ads, they increased overall ROI by 190%.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Lightbulb className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        Modern marketing funnels are complex, but they're also more effective when properly understood and optimized. Focus on mapping actual customer journeys, measuring multi-touch attribution, and optimizing each stage for maximum impact. The businesses that master this complexity will dominate their markets in 2024 and beyond.
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
                        Free Funnel Tools
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <p className="text-sm text-muted-foreground">
                        Analyze and optimize your marketing funnel.
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
                        <span className="font-medium">10 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">Funnel Strategy</Badge>
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

export default MarketingFunnelHowItWorks;
