
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
  Target,
  Heart,
  DollarSign
} from "lucide-react";

const BrandingPerformanceMarketing = () => {
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
              Brand Strategy
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Why branding and performance marketing aren't opposites
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Breaking down the false dichotomy between brand building and performance marketing - discover how the best companies excel at both simultaneously.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 10, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                9 min read
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
                alt="Branding and performance marketing integration"
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
                  <div className="bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Lightbulb className="h-6 w-6 text-purple-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        One of the most damaging myths in marketing is that branding and performance marketing are opposing forces. This false dichotomy has led countless Rotterdam businesses to choose between building brand equity and driving immediate results. The truth is that the most successful companies seamlessly integrate both approaches, creating synergies that amplify their overall marketing effectiveness.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">The False Dichotomy Hurting Your Business</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Traditional marketing education has created an artificial separation between branding (building long-term brand equity) and performance marketing (driving immediate, measurable results). This has led to organizational silos where brand teams focus on awareness and perception while performance teams focus on conversions and ROI.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This separation is not just counterproductive - it's actively harmful. When branding and performance marketing operate in isolation, companies miss the compounding effects that occur when these disciplines work together. Rotterdam businesses implementing integrated approaches see 45% higher marketing efficiency compared to those with siloed strategies.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      The companies winning in today's market understand that every brand touchpoint influences performance, and every performance campaign impacts brand perception. They've moved beyond the outdated either/or mentality to embrace a both/and approach that maximizes total business impact.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">How Brand Strength Amplifies Performance Marketing</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Strong brands don't just feel good - they perform better across every marketing metric. Companies with high brand strength see 23% higher click-through rates, 31% higher conversion rates, and 41% higher customer lifetime value compared to weak brands in the same categories.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Brand recognition reduces the friction in performance marketing campaigns. When prospects already know and trust your brand, they're more likely to click your ads, engage with your content, and convert on your offers. This means your performance marketing budget works harder and delivers better results.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam tech company discovered this principle firsthand when they invested in brand building alongside their performance campaigns. After six months of integrated branding and performance efforts, their cost per acquisition dropped by 38% while their conversion rates increased by 52%. The brand investment didn't compete with performance marketing - it supercharged it.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">How Performance Marketing Strengthens Brand Building</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Performance marketing doesn't just drive immediate results - it provides invaluable data that makes brand building more effective. Every performance campaign generates insights about what messages resonate, which audiences respond, and what creative elements drive action.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This performance data should inform brand strategy, not operate separately from it. The messages that drive conversions often reveal core brand truths that should be amplified across all marketing efforts. The audiences that convert become the foundation for broader brand targeting and positioning.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Smart Dutch companies use performance marketing as a testing ground for brand messages. They identify high-performing creative elements in their performance campaigns and then scale those insights across their brand marketing efforts. This data-driven approach to brand building eliminates guesswork and ensures brand investments are based on proven performance insights.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">Building an Integrated Marketing Approach</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Successful integration requires breaking down organizational silos and creating shared objectives that span both brand and performance goals. This means establishing metrics that capture both immediate performance and long-term brand health.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      The most effective integrated strategies involve creating brand-consistent performance campaigns and performance-informed brand campaigns. Every touchpoint should advance both immediate conversion goals and long-term brand objectives simultaneously.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Rotterdam companies leading this integration are seeing remarkable results: 67% improvement in overall marketing ROI, 34% increase in brand metrics, and 45% better customer retention rates. They've proven that the choice between branding and performance marketing is a false one - the real opportunity lies in doing both exceptionally well.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        The most successful marketing strategies don't choose between branding and performance - they integrate both approaches to create compound effects that amplify overall business results. Companies that embrace this integration see higher performance metrics, stronger brand equity, and superior long-term growth.
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
                        Optimize your integrated marketing approach.
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
                        <span className="font-medium">9 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">Brand Strategy</Badge>
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

export default BrandingPerformanceMarketing;
