
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
  Timer,
  Rocket,
  Target
} from "lucide-react";

const LowHangingGrowthAi = () => {
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
              Quick Wins
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Low-hanging growth: what you can improve tomorrow using AI
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Quick wins and immediate improvements you can implement with AI tools - start seeing results within 24 hours of reading this guide.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 6, 2024
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                6 min read
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
                src="https://images.unsplash.com/photo-1531297484001-80022131f5a1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="Quick AI improvements for business growth - laptop with analytics"
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
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Timer className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        You don't need months of planning or massive budgets to start benefiting from AI marketing. There are several quick wins that Rotterdam businesses can implement tomorrow to see immediate improvements in efficiency, customer experience, and results. These low-hanging fruit opportunities require minimal setup but deliver measurable impact within 24-48 hours.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">Automated Email Subject Line Optimization</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Email subject lines determine whether your carefully crafted messages get opened or ignored. AI tools can analyze your email performance data and automatically suggest high-performing subject line variations based on your audience's behavior patterns.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Implementation time: 30 minutes. Expected improvement: 15-25% increase in open rates within one week. Tools like Mailchimp's Smart Subject Lines or HubSpot's AI-powered email optimization can be set up immediately and start generating better subject lines for your next campaign.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam e-commerce company implemented AI subject line optimization and saw their email open rates jump from 18% to 23% in just five days. The tool automatically tested variations like "Your order is ready" vs "Good news about your recent purchase" and optimized based on actual customer responses.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Smart Chatbot for Common Questions</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Most businesses receive the same 10-15 questions repeatedly via email, phone, or social media. An AI chatbot can handle these common inquiries instantly, freeing up your team for more complex customer interactions while providing immediate responses to visitors.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Implementation time: 2-3 hours. Expected improvement: 40-60% reduction in basic customer service inquiries, 24/7 availability for customer support. Platforms like Intercom, Tidio, or ChatGPT-powered solutions can be configured with your FAQ content and deployed immediately.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch service company implemented a simple AI chatbot that answered questions about pricing, hours, and service areas. Within 48 hours, the chatbot was handling 67% of their customer inquiries, allowing their human team to focus on complex problem-solving and sales conversations.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">Automatic Social Media Post Scheduling</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Consistent social media presence requires regular posting, but manually scheduling content is time-consuming. AI tools can analyze your audience's online behavior to determine optimal posting times and automatically schedule your content for maximum engagement.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Implementation time: 1 hour. Expected improvement: 20-35% increase in social media engagement, 80% reduction in manual posting time. Tools like Buffer's AI optimization or Hootsuite's best time recommendations can start optimizing your social presence immediately.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam restaurant used AI scheduling to optimize their Instagram posts and discovered their customers were most active at 11:30 AM and 6:15 PM - times they had never posted before. By automatically posting at these AI-recommended times, their engagement increased by 34% in two weeks.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">AI-Powered Website Personalization</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Your website can automatically adapt its content, offers, and layout based on visitor behavior and characteristics. Simple AI personalization tools can show different content to first-time visitors versus returning customers, or adjust messaging based on the visitor's geographic location or device type.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Implementation time: 2-4 hours. Expected improvement: 12-20% increase in conversion rates, 25-40% improvement in time on site. Tools like Optimizely, Google Optimize, or Dynamic Yield offer easy-to-implement personalization features that start working immediately.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Dutch B2B software company implemented basic AI personalization that showed different case studies to visitors from different industries. This simple change increased their demo request rate by 28% within the first week, with manufacturing visitors seeing manufacturing case studies and retail visitors seeing retail examples.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <Rocket className="h-6 w-6 text-blue-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        These quick AI wins require minimal investment but deliver immediate, measurable improvements. Start with one implementation tomorrow, measure the results, then add the next improvement. Small AI optimizations compound quickly into significant business advantages.
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
                        Implement these quick wins with our free tools.
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
                        <span className="font-medium">6 min</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">Quick Wins</Badge>
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

export default LowHangingGrowthAi;
