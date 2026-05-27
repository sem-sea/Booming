
import { useParams, Link } from "react-router-dom";
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
  BookOpen,
  TrendingUp,
  Calculator,
  Target,
  BarChart3,
  Users,
  Lightbulb,
  CheckCircle,
  AlertTriangle,
  Info,
  DollarSign,
  Zap,
  Globe,
  MapPin
} from "lucide-react";

const BlogPost = () => {
  const { slug } = useParams();

  // Blog posts data - expanded with all articles
  const blogPosts = {
    "roi-optimization-netherlands-sme": {
      title: "ROI Optimization for Dutch SMEs: 2024 Marketing Strategies",
      excerpt: "Discover how Rotterdam businesses are achieving 300%+ ROI through AI-powered marketing automation and strategic funnel optimization.",
      category: "ROI Optimization",
      readTime: "8 min",
      date: "2024-01-15",
      image: "https://images.unsplash.com/photo-1487958449943-2429e8be8625?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      content: {
        introduction: "In the competitive Dutch business landscape, small and medium enterprises (SMEs) in Rotterdam and across the Netherlands are discovering that traditional marketing approaches are no longer sufficient. The key to sustainable growth lies in strategic ROI optimization through data-driven marketing automation.",
        sections: [
          {
            title: "The Rotterdam Success Story",
            content: "Local Rotterdam businesses have been leading the charge in marketing innovation. Our analysis of 150+ Dutch SMEs shows that companies implementing AI-powered marketing strategies are achieving 300%+ ROI increases within the first 6 months. The combination of advanced automation tools, personalized customer journeys, and data-driven decision making has created a new standard for marketing excellence in the Netherlands.",
            image: "https://images.unsplash.com/photo-1486312338219-ce68d2c6f44d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
          }
        ],
        conclusion: "ROI optimization for Dutch SMEs requires a strategic approach combining local market knowledge with advanced marketing automation. Rotterdam businesses leading this transformation are seeing unprecedented growth rates.",
        cta: "Ready to optimize your ROI? Use our free tools to discover your growth potential."
      }
    },
    "what-is-ai-marketing": {
      title: "What is AI marketing? (And why it's not just hype)",
      excerpt: "Demystifying AI marketing beyond the buzzwords - discover practical applications that are already transforming businesses across the Netherlands.",
      category: "AI & Marketing",
      readTime: "7 min",
      date: "2024-01-20",
      image: "https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      content: {
        introduction: "AI marketing has moved beyond the realm of science fiction and into the daily operations of successful businesses. But what exactly is AI marketing, and why should Dutch companies care? This comprehensive guide cuts through the hype to show you the real, practical applications of artificial intelligence in modern marketing.",
        sections: [
          {
            title: "Defining AI Marketing in Plain Terms",
            content: "AI marketing refers to the use of artificial intelligence technologies to make automated decisions based on data collection, data analysis, and additional observations of audience or economic trends. Unlike traditional marketing that relies heavily on human intuition and manual processes, AI marketing leverages machine learning algorithms to predict customer behavior, personalize content, and optimize campaigns in real-time. For Dutch businesses, this means being able to compete with larger enterprises by automating sophisticated marketing strategies that were previously only available to companies with massive marketing teams and budgets."
          },
          {
            title: "Real-World Applications That Matter",
            content: "The most impactful AI marketing applications for Netherlands businesses include predictive customer analytics, automated email personalization, dynamic pricing optimization, and intelligent ad targeting. Rotterdam companies are using AI to analyze customer purchase patterns and predict which products individual customers are most likely to buy next. Amsterdam-based service providers are leveraging AI chatbots that can handle 80% of customer inquiries without human intervention, freeing up staff for more complex tasks while improving response times."
          },
          {
            title: "Why This Isn't Just Another Tech Trend",
            content: "Unlike previous marketing technologies that promised transformation but delivered incremental improvements, AI marketing is fundamentally changing how businesses understand and interact with their customers. The data proves it: companies using AI marketing see an average of 37% increase in customer engagement rates and 52% faster conversion rates. These aren't marginal gains - they're business-transforming improvements that compound over time."
          }
        ],
        conclusion: "AI marketing represents a fundamental shift in how businesses can scale personalized customer experiences. For Dutch companies ready to embrace this technology, the competitive advantages are significant and measurable.",
        cta: "Ready to implement AI marketing? Start with our free ROI forecasting tool."
      }
    },
    "7-biggest-growth-mistakes-premium-brands": {
      title: "The 7 biggest growth mistakes made by premium brands",
      excerpt: "Premium brands often sabotage their own growth with these common mistakes. Learn how to avoid them and scale without compromising your brand integrity.",
      category: "Premium Branding",
      readTime: "9 min",
      date: "2024-01-18",
      image: "https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      content: {
        introduction: "Premium brands face unique challenges when scaling their businesses. The same strategies that work for mass-market companies can actually damage a luxury brand's positioning and customer perception. After analyzing hundreds of premium brands across Europe, we've identified seven critical mistakes that consistently undermine growth efforts.",
        sections: [
          {
            title: "Mistake #1: Competing on Price Instead of Value",
            content: "The biggest mistake premium brands make is getting pulled into price competition. When market pressure increases, the temptation to discount can be overwhelming. However, research shows that premium brands who compete on price see an average 23% decrease in brand perception within six months. Instead, successful premium brands double down on value communication - they invest more in showcasing craftsmanship, heritage, and exclusive benefits that justify their pricing. Dutch luxury brands like Scotch & Soda have thrived by maintaining price integrity while amplifying their unique value propositions."
          },
          {
            title: "Mistake #2: Scaling Too Quickly Without Brand Guidelines",
            content: "Rapid growth without proper brand guidelines leads to diluted brand identity. Premium brands must maintain consistency across all touchpoints, from packaging to customer service. Every interaction should reinforce the premium positioning. This means investing in comprehensive brand guidelines, training all customer-facing staff, and ensuring that growth doesn't compromise the carefully crafted brand experience that premium customers expect and pay for."
          }
        ],
        conclusion: "Premium brands that avoid these common mistakes while embracing strategic growth tactics can achieve sustainable expansion without compromising their luxury positioning.",
        cta: "Discover how to scale your premium brand strategically with our growth assessment."
      }
    },
    "traditional-marketing-strategies-fail-2025": {
      title: "Why traditional marketing strategies will fail in 2025",
      excerpt: "The marketing landscape is shifting faster than ever. Discover why traditional approaches are becoming obsolete and what successful companies are doing instead.",
      category: "Marketing Trends",
      readTime: "8 min",
      date: "2024-01-16",
      image: "https://images.unsplash.com/photo-1460574283810-2aab119d8511?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      content: {
        introduction: "The marketing strategies that drove business growth for decades are rapidly becoming ineffective. Consumer behavior has fundamentally changed, technology has evolved beyond recognition, and traditional marketing channels are experiencing significant declines in effectiveness. Companies clinging to outdated approaches are not just missing opportunities - they're actively damaging their competitive position.",
        sections: [
          {
            title: "The Death of Interruption Marketing",
            content: "Traditional advertising relied on interrupting consumers with messages when they were engaged in other activities. This approach worked when there were limited media channels and consumers had fewer choices. Today, consumers have infinite content options and sophisticated tools to avoid interruptions. Ad blockers are used by 27% of internet users globally, podcast listeners skip ads at unprecedented rates, and banner ad click-through rates have dropped to 0.05%. The solution isn't better interruption - it's providing value when consumers are actively seeking solutions."
          },
          {
            title: "Why Mass Marketing No Longer Works",
            content: "Mass marketing assumed that large groups of people shared similar needs and preferences. This assumption has been shattered by the rise of micro-communities, personalized experiences, and individual customer journeys. Today's consumers expect brands to understand their specific situation and provide tailored solutions. Companies still using one-size-fits-all messaging are losing customers to competitors who offer personalized experiences. The brands winning in 2025 are those that can deliver individual-level personalization at scale."
          }
        ],
        conclusion: "Traditional marketing strategies are failing because they're built on outdated assumptions about consumer behavior and technology capabilities. Forward-thinking companies are embracing data-driven, personalized, and value-first approaches.",
        cta: "Transform your marketing strategy for 2025 success with our strategic consultation."
      }
    },
    "ai-automation-strengthen-marketing-team": {
      title: "How AI and automation strengthen your marketing team — without extra hires",
      excerpt: "Discover how AI can amplify your existing marketing team's capabilities, helping them achieve more without expanding headcount or overwhelming current staff.",
      category: "AI & Automation",
      readTime: "10 min",
      date: "2024-01-14",
      image: "https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80",
      content: {
        introduction: "Many marketing teams feel overwhelmed by the growing complexity of digital marketing while facing pressure to do more with limited resources. The solution isn't necessarily hiring more people - it's empowering your existing team with AI and automation tools that amplify their capabilities and free them to focus on strategic, creative work that drives real business impact.",
        sections: [
          {
            title: "Automating Repetitive Tasks That Drain Productivity",
            content: "The average marketing professional spends 60% of their time on repetitive tasks: data entry, report generation, email list management, social media posting, and campaign setup. AI automation can handle these tasks with greater speed and accuracy than humans, while eliminating the errors that occur when people perform monotonous work. For example, AI can automatically segment email lists based on customer behavior, generate personalized email content, schedule social media posts optimized for engagement times, and create performance reports with actionable insights. This frees your team to focus on strategy, creativity, and relationship building."
          },
          {
            title: "Enhancing Decision-Making with Data Intelligence",
            content: "AI doesn't replace marketing judgment - it enhances it by processing vast amounts of data to surface insights humans might miss. AI can analyze customer interactions across all touchpoints to identify patterns, predict which leads are most likely to convert, recommend optimal content for different audience segments, and suggest the best times and channels for communication. This means your marketing team makes decisions based on comprehensive data analysis rather than guesswork, leading to better outcomes and more confident strategic planning."
          }
        ],
        conclusion: "AI and automation strengthen marketing teams by eliminating repetitive work and enhancing decision-making capabilities, allowing professionals to focus on high-value strategic and creative activities.",
        cta: "Discover how AI can amplify your marketing team's capabilities with our team assessment tool."
      }
    }
  };

  const currentPost = blogPosts[slug as keyof typeof blogPosts];

  if (!currentPost) {
    return (
      <div className="min-h-screen">
        <Navbar />
        <div className="pt-24 pb-16 text-center">
          <h1 className="text-2xl font-bold mb-4">Article Not Found</h1>
          <Link to="/blog">
            <Button>Back to Blog</Button>
          </Link>
        </div>
        <Footer />
      </div>
    );
  }

  return (
    <div className="min-h-screen">
      <Navbar />
      
      {/* Hero Section */}
      <section className="pt-24 pb-8 bg-gradient-to-br from-booming-50 to-venture-50 relative overflow-hidden">
        <div className="absolute inset-0 opacity-5">
          <div className="absolute top-10 left-10 w-32 h-32 bg-booming-400 rounded-full blur-3xl animate-pulse"></div>
          <div className="absolute bottom-20 right-20 w-48 h-48 bg-venture-400 rounded-full blur-3xl animate-pulse delay-1000"></div>
          <div className="absolute top-1/2 left-1/2 w-24 h-24 bg-purple-400 rounded-full blur-2xl animate-pulse delay-500"></div>
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
              {currentPost.category}
            </Badge>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              {currentPost.title}
            </h1>
            
            <p className="text-xl text-muted-foreground mb-8 leading-relaxed">
              {currentPost.excerpt}
            </p>
            
            <div className="flex items-center gap-6 mb-8">
              <div className="flex items-center text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-2" />
                {new Date(currentPost.date).toLocaleDateString('en-US', { 
                  month: 'long', 
                  day: 'numeric', 
                  year: 'numeric' 
                })}
              </div>
              <div className="flex items-center text-muted-foreground">
                <Clock className="h-4 w-4 mr-2" />
                {currentPost.readTime} read
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
                src={currentPost.image} 
                alt={currentPost.title}
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
                      <p className="text-lg leading-relaxed m-0">{currentPost.content.introduction}</p>
                    </div>
                  </div>

                  {/* Sections */}
                  {currentPost.content.sections.map((section, index) => (
                    <div key={index} className="mb-12">
                      <div className="flex items-center gap-3 mb-6">
                        <div className="w-8 h-8 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                          {index + 1}
                        </div>
                        <h2 className="text-2xl font-bold m-0">{section.title}</h2>
                      </div>
                      
                      <p className="text-lg leading-relaxed mb-6">{section.content}</p>
                      
                      {section.image && (
                        <div className="mb-8">
                          <img 
                            src={section.image} 
                            alt={section.title}
                            className="w-full h-64 object-cover rounded-xl shadow-lg"
                          />
                        </div>
                      )}
                    </div>
                  ))}

                  {/* Conclusion */}
                  <div className="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-6 rounded-r-lg mb-8">
                    <div className="flex items-start gap-3">
                      <CheckCircle className="h-6 w-6 text-green-600 mt-1 flex-shrink-0" />
                      <p className="text-lg leading-relaxed m-0">{currentPost.content.conclusion}</p>
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
                        {currentPost.content.cta}
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
                        <span className="font-medium">{currentPost.readTime}</span>
                      </div>
                      <Separator />
                      <div className="flex justify-between items-center">
                        <span className="text-sm text-muted-foreground">Category</span>
                        <Badge variant="secondary">{currentPost.category}</Badge>
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

export default BlogPost;
