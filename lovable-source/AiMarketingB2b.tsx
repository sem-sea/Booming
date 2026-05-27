
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
  MapPin,
  Building,
  Shield,
  Target
} from "lucide-react";

const AiMarketingB2b = () => {
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
              B2B Marketing
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              AI marketing for B2B: opportunities, risks, and results
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Navigate the complex world of B2B AI marketing with confidence - understand the opportunities, mitigate the risks, and achieve measurable results.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 8, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                12 min read
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
                src="https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="AI marketing for B2B businesses"
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
                  <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Building className="h-6 w-6 text-blue-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        B2B marketing presents unique challenges that make AI implementation both more complex and more valuable than in B2C contexts. Longer sales cycles, multiple decision-makers, and higher-stakes purchases require sophisticated approaches that balance automation with human relationship building. Dutch B2B companies are discovering that AI can transform their marketing effectiveness while respecting the nuanced nature of business relationships.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">The Unique B2B AI Opportunity</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      B2B marketing generates more data per prospect than B2C marketing, creating rich opportunities for AI analysis. Every email interaction, content download, website visit, and sales conversation provides valuable signals about buying intent and decision-maker preferences.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This data density allows AI to create sophisticated prospect profiles that go far beyond basic demographics. AI can analyze behavioral patterns to identify buying committee members, predict purchase timing, and recommend optimal outreach strategies for different stakeholders within target organizations.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Rotterdam B2B companies using AI for lead scoring report 89% improvement in sales qualified lead quality and 47% reduction in sales cycle length. The key is leveraging AI to understand complex B2B buying processes rather than simply automating basic marketing tasks.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Critical Risk Management for B2B AI Marketing</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      B2B relationships are built on trust and expertise, making the risks of AI marketing mistakes significantly higher than in B2C contexts. A poorly targeted email or inappropriate automated response can damage relationships that took years to build and represent significant revenue potential.
                    </p>

                    <div className="bg-gradient-to-r from-orange-50 to-red-50 border-l-4 border-orange-500 p-6 rounded-r-lg mb-6">
                      <div className="flex items-start gap-3">
                        <AlertTriangle className="h-6 w-6 text-orange-600 mt-1 flex-shrink-0" />
                        <div>
                          <p className="text-lg leading-relaxed m-0 mb-4">
                            <strong>Key B2B AI Risks to Avoid:</strong>
                          </p>
                          <ul className="text-base leading-relaxed m-0 space-y-2">
                            <li>• Over-automation that eliminates human touchpoints in relationship building</li>
                            <li>• Generic messaging that fails to reflect industry-specific expertise</li>
                            <li>• Inappropriate timing of automated outreach during sensitive business periods</li>
                            <li>• Data privacy violations that could damage enterprise client relationships</li>
                          </ul>
                        </div>
                      </div>
                    </div>

                    <p className="text-lg leading-relaxed mb-6">
                      Successful B2B AI implementation requires robust human oversight and clear escalation protocols. AI should enhance human decision-making, not replace it, especially for high-value prospects and sensitive account situations.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">Proven B2B AI Marketing Applications</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      The most successful B2B AI applications focus on enhancing human capabilities rather than replacing human judgment. Account-based marketing (ABM) sees particular benefits from AI-powered personalization and timing optimization.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      AI excels at analyzing multiple data sources to identify the optimal moment for sales outreach, the most relevant content for specific buying committee members, and the communication channels most likely to generate engagement. This intelligence allows sales and marketing teams to focus their efforts on the highest-impact activities.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch software company used AI to analyze their prospect engagement data and discovered that C-level executives preferred technical white papers delivered via LinkedIn, while IT decision-makers responded better to case studies sent via email. This insight led to 156% improvement in engagement rates and 78% increase in qualified opportunities.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">Measuring B2B AI Marketing Success</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      B2B AI marketing success requires metrics that go beyond traditional conversion rates. The long sales cycles and high customer values in B2B contexts mean that early indicators of success often matter more than immediate conversions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Key performance indicators should include lead quality scores, sales cycle acceleration, customer lifetime value improvement, and account penetration depth. These metrics better reflect the true impact of AI on complex B2B sales processes.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Rotterdam B2B companies implementing comprehensive AI measurement frameworks report average improvements of 43% in pipeline quality, 67% reduction in time to qualified opportunity, and 89% increase in account expansion rates. The key is measuring what matters for long-term B2B success, not just short-term activity metrics.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        B2B AI marketing success requires balancing automation capabilities with human relationship management. Companies that implement AI thoughtfully, with proper risk management and success measurement, achieve significant improvements in sales efficiency and customer acquisition while maintaining the trust and expertise that B2B relationships require.
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
                        Optimize your B2B marketing with AI insights.
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
                        <span className="font-medium">12 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">B2B Marketing</Badge>
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

export default AiMarketingB2b;
