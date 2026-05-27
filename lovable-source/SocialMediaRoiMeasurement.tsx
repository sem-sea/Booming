
import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, BarChart3, Target, DollarSign } from "lucide-react";

const SocialMediaRoiMeasurement = () => {
  useEffect(() => {
    document.title = "How to Measure Social Media ROI (The Right Way) | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "How to measure social media ROI (the right way)",
      "description": "Stop relying on vanity metrics. Learn how to track social media ROI that actually correlates with business growth and revenue.",
      "author": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Booming Venture",
        "address": {
          "@type": "PostalAddress",
          "addressLocality": "Rotterdam",
          "addressCountry": "NL"
        }
      },
      "datePublished": "2024-01-26",
      "dateModified": "2024-01-26"
    };
    
    const script = document.createElement('script');
    script.type = 'application/ld+json';
    script.text = JSON.stringify(structuredData);
    document.head.appendChild(script);
    
    return () => {
      document.head.removeChild(script);
    };
  }, []);

  return (
    <div className="min-h-screen">
      <Navbar />
      
      <article className="pt-24 pb-16">
        <div className="container mx-auto px-4 md:px-6 max-w-4xl">
          <Link to="/blog" className="inline-flex items-center text-booming-600 hover:text-booming-700 mb-8 group">
            <ArrowLeft className="h-4 w-4 mr-2 group-hover:-translate-x-1 transition-transform" />
            Back to Blog
          </Link>
          
          <motion.header
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="mb-12"
          >
            <div className="flex items-center gap-4 mb-6">
              <Badge variant="secondary" className="bg-gradient-to-r from-pink-500 to-purple-500 text-white">
                Social Media
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                January 26, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                9 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              How to measure social media ROI (the right way)
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Stop relying on vanity metrics. Learn how to track social media ROI that actually correlates with business growth and revenue.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1611224923853-80b023f02d71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Social media analytics dashboard"
              className="w-full h-[400px] object-cover rounded-lg shadow-lg"
            />
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="prose prose-lg max-w-none"
          >
            <p className="text-lg leading-relaxed mb-8">
              "Our Instagram has 50K followers!" "We got 10,000 likes on that post!" "Our reach increased by 200%!" These statements make marketing managers feel good, but they don't pay the bills. If you're measuring social media success with vanity metrics, you're flying blind.
            </p>
            
            <div className="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
              <h3 className="text-xl font-bold mb-4 text-red-800">The Vanity Metrics Trap</h3>
              <p className="text-red-700 mb-4">
                73% of Dutch SMEs measure social media success through followers, likes, and reach. Yet only 23% can directly connect their social media efforts to revenue growth.
              </p>
              <p className="text-red-700 font-medium">
                The disconnect? They're measuring activity, not impact.
              </p>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">The Business Impact Framework</h2>
            
            <p className="mb-6">
              True social media ROI measurement requires connecting social activity to business outcomes. Here's the framework successful Rotterdam businesses use:
            </p>
            
            <div className="grid md:grid-cols-3 gap-6 mb-8">
              <Card className="border-t-4 border-t-blue-500">
                <CardContent className="p-6">
                  <BarChart3 className="h-12 w-12 text-blue-600 mb-4" />
                  <h4 className="text-lg font-bold mb-4 text-blue-700">Revenue Metrics</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Direct sales attribution</li>
                    <li>• Lead generation value</li>
                    <li>• Customer lifetime value</li>
                    <li>• Conversion rate by platform</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <Target className="h-12 w-12 text-green-600 mb-4" />
                  <h4 className="text-lg font-bold mb-4 text-green-700">Engagement Quality</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Comments-to-followers ratio</li>
                    <li>• Share/save rates</li>
                    <li>• Click-through rates</li>
                    <li>• Time spent on website</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-purple-500">
                <CardContent className="p-6">
                  <DollarSign className="h-12 w-12 text-purple-600 mb-4" />
                  <h4 className="text-lg font-bold mb-4 text-purple-700">Cost Efficiency</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Cost per acquisition</li>
                    <li>• Cost per lead</li>
                    <li>• Ad spend efficiency</li>
                    <li>• Organic reach value</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Setting Up Proper Attribution</h2>
            
            <p className="mb-6">
              The biggest challenge in social media ROI measurement isn't collecting data—it's connecting social interactions to actual business outcomes. Here's how to build proper attribution:
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">1. UTM Parameters: Your Tracking Foundation</h3>
            
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-blue-800">UTM Parameter Structure for Social Media:</h4>
              <code className="block bg-white p-4 rounded border text-sm mb-4">
                ?utm_source=linkedin&utm_medium=social&utm_campaign=lead_gen_q1&utm_content=video_post&utm_term=marketing_automation
              </code>
              <p className="text-blue-700">
                This structure allows you to track exactly which social post, platform, and campaign generated each conversion.
              </p>
            </div>
            
            <h3 className="text-2xl font-semibold mb-4">2. Multi-Touch Attribution</h3>
            
            <p className="mb-6">
              B2B customers typically interact with your brand 7-11 times before purchasing. Social media often plays multiple roles in this journey—awareness, consideration, and decision. Single-touch attribution misses this complexity.
            </p>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "We discovered that LinkedIn generated 40% of our leads, but Instagram influenced 60% of our high-value conversions. Without multi-touch attribution, we would have cut Instagram spending." - Rotterdam B2B Agency
            </blockquote>
            
            <h2 className="text-3xl font-bold mb-6">Platform-Specific ROI Formulas</h2>
            
            <p className="mb-6">
              Different platforms serve different purposes in your marketing funnel. Here are the ROI formulas that matter for each:
            </p>
            
            <div className="space-y-6 mb-8">
              <Card className="border-l-4 border-l-blue-600">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold mb-4 text-blue-700">LinkedIn (B2B Lead Generation)</h4>
                  <p className="font-mono text-sm bg-gray-100 p-3 rounded mb-3">
                    ROI = ((Lead Value × Conversion Rate) - Campaign Cost) / Campaign Cost × 100
                  </p>
                  <p className="text-gray-700">Focus on lead quality over quantity. Track leads through to closed deals.</p>
                </CardContent>
              </Card>
              
              <Card className="border-l-4 border-l-pink-500">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold mb-4 text-pink-700">Instagram (Brand Awareness & E-commerce)</h4>
                  <p className="font-mono text-sm bg-gray-100 p-3 rounded mb-3">
                    ROI = (Revenue Attributed + Brand Value Increase - Total Cost) / Total Cost × 100
                  </p>
                  <p className="text-gray-700">Include brand awareness value using surveys and search volume increases.</p>
                </CardContent>
              </Card>
              
              <Card className="border-l-4 border-l-green-600">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold mb-4 text-green-700">Facebook (Conversion & Retargeting)</h4>
                  <p className="font-mono text-sm bg-gray-100 p-3 rounded mb-3">
                    ROI = (Conversion Value - Ad Spend - Time Investment) / Total Investment × 100
                  </p>
                  <p className="text-gray-700">Facebook's pixel data provides the most accurate direct attribution.</p>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Tools and Implementation</h2>
            
            <p className="mb-6">
              Measuring social media ROI requires the right tools working together. Here's the tech stack used by successful Dutch businesses:
            </p>
            
            <div className="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4">Essential Tool Stack:</h4>
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <strong className="text-gray-800">Analytics & Attribution:</strong>
                  <ul className="text-sm text-gray-600 mt-2">
                    <li>• Google Analytics 4</li>
                    <li>• Facebook Analytics</li>
                    <li>• LinkedIn Campaign Manager</li>
                    <li>• UTM.io for link management</li>
                  </ul>
                </div>
                <div>
                  <strong className="text-gray-800">CRM Integration:</strong>
                  <ul className="text-sm text-gray-600 mt-2">
                    <li>• HubSpot (free tier available)</li>
                    <li>• Pipedrive</li>
                    <li>• Custom tracking sheets</li>
                    <li>• Zapier for automation</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">The 90-Day Implementation Plan</h2>
            
            <p className="mb-6">
              Transforming your social media measurement from vanity metrics to business impact doesn't happen overnight. Here's a proven 90-day plan:
            </p>
            
            <div className="grid md:grid-cols-3 gap-6 mb-8">
              <Card className="border-t-4 border-t-red-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-red-700">Days 1-30: Foundation</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Set up proper tracking</li>
                    <li>• Define business objectives</li>
                    <li>• Establish baseline metrics</li>
                    <li>• Implement UTM parameters</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-orange-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-orange-700">Days 31-60: Optimization</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Analyze initial data</li>
                    <li>• Adjust content strategy</li>
                    <li>• Optimize conversion paths</li>
                    <li>• A/B test approaches</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-green-700">Days 61-90: Scale</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Scale successful tactics</li>
                    <li>• Create automated reports</li>
                    <li>• Set up alerts and KPIs</li>
                    <li>• Plan next quarter</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Making It All Work Together</h2>
            
            <p className="mb-6">
              The goal isn't to track everything—it's to track what matters for your specific business model. A Rotterdam restaurant needs different metrics than a B2B software company. Focus on the metrics that directly correlate with your revenue goals.
            </p>
            
            <p className="text-lg font-medium">
              Remember: Good social media ROI measurement transforms marketing from a cost center into a profit driver.
            </p>
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.6 }}
            className="mt-16"
          >
            <Card className="bg-gradient-to-r from-booming-600 to-venture-600 text-white border-none">
              <CardContent className="p-8 text-center">
                <h3 className="text-2xl font-bold mb-4">Start Measuring Real ROI</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Get a free social media ROI audit and discover your true return on investment
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <BarChart3 className="h-5 w-5 mr-2" />
                      Analyze My Funnel
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-white hover:bg-white/10 px-8 py-3">
                      Get Expert Help
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </motion.div>
        </div>
      </article>
      
      <Footer />
    </div>
  );
};

export default SocialMediaRoiMeasurement;
