
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
  Users,
  Target,
  MapPin
} from "lucide-react";

const AiAutomationMarketingTeam = () => {
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
              AI & Automation
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              How AI and automation strengthen your marketing team — without extra hires
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              Discover how AI can amplify your existing marketing team's capabilities, helping them achieve more without expanding headcount or overwhelming current staff.
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                January 14, 2024
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
                src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" 
                alt="AI strengthening marketing team capabilities"
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
                        Many marketing teams feel overwhelmed by the growing complexity of digital marketing while facing pressure to do more with limited resources. The solution isn't necessarily hiring more people - it's empowering your existing team with AI and automation tools that amplify their capabilities and free them to focus on strategic, creative work that drives real business impact.
                      </p>
                    </div>
                  </div>

                  {/* Section 1 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        1
                      </div>
                      <h2 className="text-2xl font-bold m-0">Automating Repetitive Tasks That Drain Productivity</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      The average marketing professional spends 60% of their time on repetitive tasks: data entry, report generation, email list management, social media posting, and campaign setup. AI automation can handle these tasks with greater speed and accuracy than humans, while eliminating the errors that occur when people perform monotonous work.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      For example, AI can automatically segment email lists based on customer behavior, generate personalized email content, schedule social media posts optimized for engagement times, and create performance reports with actionable insights. This frees your team to focus on strategy, creativity, and relationship building.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Rotterdam marketing teams using AI automation report saving 15-20 hours per week on routine tasks. One local e-commerce company automated their email marketing workflows and saw their marketing coordinator go from spending 3 hours daily on email management to just 30 minutes, allowing them to focus on campaign strategy and customer experience optimization.
                    </p>
                  </div>

                  {/* Section 2 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        2
                      </div>
                      <h2 className="text-2xl font-bold m-0">Enhancing Decision-Making with Data Intelligence</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      AI doesn't replace marketing judgment - it enhances it by processing vast amounts of data to surface insights humans might miss. AI can analyze customer interactions across all touchpoints to identify patterns, predict which leads are most likely to convert, recommend optimal content for different audience segments, and suggest the best times and channels for communication.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This means your marketing team makes decisions based on comprehensive data analysis rather than guesswork, leading to better outcomes and more confident strategic planning. Instead of spending hours manually analyzing campaign performance, your team gets instant insights with recommended actions.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Dutch businesses implementing AI-driven decision support see average conversion rate improvements of 43%. Marketing managers report feeling more confident in their strategic recommendations because they have data-backed insights to support their proposals to leadership.
                    </p>
                  </div>

                  {/* Section 3 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        3
                      </div>
                      <h2 className="text-2xl font-bold m-0">Scaling Personalization Without Additional Resources</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      Personalization drives results, but manually creating personalized content for thousands of customers is impossible for small teams. AI solves this by analyzing customer data to automatically generate personalized emails, product recommendations, website content, and advertising messages at scale.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Your team sets the strategy and brand guidelines, while AI handles the execution across all customer touchpoints. This means every customer receives relevant, timely communications that feel personally crafted, without requiring your team to work around the clock.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      A Rotterdam SaaS company used AI to personalize their email campaigns based on user behavior and saw open rates increase by 67% and click-through rates improve by 89%. Their two-person marketing team now delivers more personalized communication than they could previously manage with a team of six.
                    </p>
                  </div>

                  {/* Section 4 */}
                  <div className="mb-12">
                    <div className="flex items-center gap-3 mb-6">
                      <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        4
                      </div>
                      <h2 className="text-2xl font-bold m-0">Continuous Optimization and Learning</h2>
                    </div>
                    
                    <p className="text-lg leading-relaxed mb-6">
                      AI systems continuously learn from campaign performance and customer interactions, automatically optimizing campaigns in real-time. While your team sleeps, AI adjusts bidding strategies, tests different content variations, and shifts budget to the highest-performing channels.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      This continuous optimization means your campaigns get better over time without manual intervention. Your team arrives each morning to campaigns that have been optimized overnight, with detailed reports explaining what changed and why.
                    </p>

                    <p className="text-lg leading-relaxed mb-6">
                      Marketing teams using AI optimization report 24/7 campaign improvements, with some seeing performance gains of 50-80% within the first month of implementation. The AI never gets tired, never forgets to check a campaign, and never misses an optimization opportunity.
                    </p>
                  </div>

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">
                        AI and automation strengthen marketing teams by eliminating repetitive work and enhancing decision-making capabilities, allowing professionals to focus on high-value strategic and creative activities that drive business growth.
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
                        Discover how AI can amplify your marketing team's capabilities.
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
                        <Badge variant="secondary">AI & Automation</Badge>
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

export default AiAutomationMarketingTeam;
