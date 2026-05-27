
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
  Globe,
  MapPin
} from "lucide-react";

const WhatIsAiMarketing = () => {
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
              AI & Marketing
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              What is AI marketing? (And why it's not just hype)
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Demystifying AI marketing beyond the buzzwords - discover practical applications that are already transforming businesses across the Netherlands.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 20, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                7 min read
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
                src="https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="AI marketing technology and strategy"
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
                  <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-booming-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Info className="h-6 w-6 text-booming-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        AI marketing has moved beyond the realm of science fiction and into the daily operations of successful businesses. But what exactly is AI marketing, and why should Dutch companies care? This comprehensive guide cuts through the hype to show you the real, practical applications of artificial intelligence in modern marketing.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">Defining AI Marketing in Plain Terms</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      AI marketing refers to the use of artificial intelligence technologies to make automated decisions based on data collection, data analysis, and additional observations of audience or economic trends. Unlike traditional marketing that relies heavily on human intuition and manual processes, AI marketing leverages machine learning algorithms to predict customer behavior, personalize content, and optimize campaigns in real-time.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      For Dutch businesses, this means being able to compete with larger enterprises by automating sophisticated marketing strategies that were previously only available to companies with massive marketing teams and budgets. Rotterdam companies are already using AI to analyze customer purchase patterns and predict which products individual customers are most likely to buy next.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Real-World Applications That Matter</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      The most impactful AI marketing applications for Netherlands businesses include predictive customer analytics, automated email personalization, dynamic pricing optimization, and intelligent ad targeting. Rotterdam companies are using AI to analyze customer purchase patterns and predict which products individual customers are most likely to buy next.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Amsterdam-based service providers are leveraging AI chatbots that can handle 80% of customer inquiries without human intervention, freeing up staff for more complex tasks while improving response times. These applications aren't futuristic concepts – they're working solutions that Dutch businesses are implementing today to drive measurable results.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">Why This Isn't Just Another Tech Trend</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Unlike previous marketing technologies that promised transformation but delivered incremental improvements, AI marketing is fundamentally changing how businesses understand and interact with their customers. The data proves it: companies using AI marketing see an average of 37% increase in customer engagement rates and 52% faster conversion rates.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      These aren't marginal gains – they're business-transforming improvements that compound over time. Dutch companies implementing AI marketing strategies are not just keeping pace with international competitors; they're often outperforming them by leveraging AI's ability to process vast amounts of customer data and deliver personalized experiences at scale.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        AI marketing represents a fundamental shift in how businesses can scale personalized customer experiences. For Dutch companies ready to embrace this technology, the competitive advantages are significant and measurable.
                      </p>
                    </div>
                  </div>
                </article>
              </div>

              {/* Sidebar */}
              <div className="lg:col-span-1">
                <div className="sticky top-24 space-y-6">
                  {/* Tools CTA */}
                  <Card className="bg-gradient-to-br from-booming-50 to-venture-50 border-booming-200">
                    <CardHeader>
                      <CardTitle className="text-lg flex items-center gap-2">
                        <Zap className="h-5 w-5 text-booming-600" />
                        Free Growth Tools
                      </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                      <p className="text-sm text-muted-foreground">
                        Ready to implement AI marketing? Start with our free ROI forecasting tool.
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

                  {/* Quick Stats */}
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
                        <span className="font-medium">7 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">AI & Marketing</Badge>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Location</span>
                        <span className="text-sm font-medium">Rotterdam, NL</span>
                      </div>
                    </CardContent>
                  </Card>

                  {/* Social Share */}
                  <Card>
                    <CardHeader>
                      <CardTitle className="text-lg flex items-center gap-2">
                        <Share2 className="h-5 w-5" />
                        Share Article
                      </CardTitle>
                    </CardHeader>
                    <CardContent>
                      <Button variant="outline" className="w-full">
                        <Globe className="h-4 w-4 mr-2" />
                        Copy Link
                      </Button>
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

export default WhatIsAiMarketing;
