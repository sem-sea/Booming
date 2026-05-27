
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
  AlertTriangle,
  Target,
  Users,
  MapPin
} from "lucide-react";

const TraditionalMarketingFail2025 = () => {
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
              Marketing Trends
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Why traditional marketing strategies will fail in 2025
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              The marketing landscape is shifting faster than ever. Discover why traditional approaches are becoming obsolete and what successful companies are doing instead.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 16, 2024
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
                src="https://images.unsplash.com/photo-1460574283810-2aab119d8511?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="Traditional marketing failing in digital age"
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
                        The marketing strategies that drove business growth for decades are rapidly becoming ineffective. Consumer behavior has fundamentally changed, technology has evolved beyond recognition, and traditional marketing channels are experiencing significant declines in effectiveness. Companies clinging to outdated approaches are not just missing opportunities - they're actively damaging their competitive position.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Death of Interruption Marketing</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Traditional advertising relied on interrupting consumers with messages when they were engaged in other activities. This approach worked when there were limited media channels and consumers had fewer choices. Today, consumers have infinite content options and sophisticated tools to avoid interruptions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Ad blockers are used by 27% of internet users globally, podcast listeners skip ads at unprecedented rates, and banner ad click-through rates have dropped to 0.05%. The solution isn't better interruption - it's providing value when consumers are actively seeking solutions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Dutch companies leading the transformation understand that permission-based marketing outperforms interruption marketing by 300%. They focus on creating content that consumers actively seek out, building trust through valuable insights, and establishing themselves as thought leaders in their industries.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Why Mass Marketing No Longer Works</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Mass marketing assumed that large groups of people shared similar needs and preferences. This assumption has been shattered by the rise of micro-communities, personalized experiences, and individual customer journeys. Today's consumers expect brands to understand their specific situation and provide tailored solutions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Companies still using one-size-fits-all messaging are losing customers to competitors who offer personalized experiences. The brands winning in 2025 are those that can deliver individual-level personalization at scale. This requires sophisticated data analysis, AI-powered content creation, and dynamic customer journey mapping.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Rotterdam businesses implementing personalization strategies see average conversion rate increases of 52%. They use AI to analyze customer behavior patterns, predict individual preferences, and deliver content that resonates with each customer's specific needs and interests.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Rise of Data-Driven Decision Making</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Traditional marketing relied heavily on intuition, experience, and broad market research. While these elements remain valuable, they're insufficient in today's rapidly changing landscape. Companies that base decisions on real-time data and predictive analytics consistently outperform those relying on traditional research methods.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Modern marketing platforms generate millions of data points about customer behavior, preferences, and engagement patterns. AI can process this information to identify trends, predict outcomes, and recommend optimizations that humans would never discover through traditional analysis.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Dutch enterprises leveraging data-driven marketing strategies report 37% higher ROI compared to those using traditional approaches. They make decisions based on customer behavior data, test hypotheses with A/B experiments, and continuously optimize campaigns based on performance metrics.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">What Successful Companies Are Doing Instead</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Forward-thinking companies are embracing AI-powered marketing automation, creating value-first content strategies, and building permission-based relationships with their audiences. They understand that modern marketing is about becoming a trusted advisor rather than a persistent salesperson.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      These companies invest in understanding their customers' complete journey, from initial awareness through post-purchase advocacy. They create content that genuinely helps customers solve problems, use AI to deliver personalized experiences at scale, and measure success through long-term customer value rather than short-term transactions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      The most successful Dutch businesses combine human creativity with AI efficiency, creating marketing strategies that are both scalable and deeply personal. They focus on building communities around their brands, fostering genuine relationships, and delivering consistent value that keeps customers engaged long-term.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        Traditional marketing strategies are failing because they're built on outdated assumptions about consumer behavior and technology capabilities. Forward-thinking companies are embracing data-driven, personalized, and value-first approaches that align with how modern consumers actually make purchasing decisions.
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
                        Free Growth Tools
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <p className="text-sm text-muted-foreground">
                        Transform your marketing strategy for 2025 success with our strategic tools.
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
                        <Badge variant="secondary">Marketing Trends</Badge>
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

export default TraditionalMarketingFail2025;
