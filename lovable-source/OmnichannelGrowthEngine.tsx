
import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, Globe, Target, Layers, CheckCircle } from "lucide-react";

const OmnichannelGrowthEngine = () => {
  useEffect(() => {
    document.title = "How to Build an Omnichannel Growth Engine in 60 Days | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "How to Build an Omnichannel Growth Engine in 60 Days",
      "description": "A step-by-step guide to creating a unified omnichannel marketing strategy that drives consistent growth across all customer touchpoints.",
      "author": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "datePublished": "2024-02-25",
      "dateModified": "2024-02-25"
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
              <Badge variant="secondary" className="bg-gradient-to-r from-booming-500 to-venture-500 text-white">
                Omnichannel Strategy
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                February 25, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                13 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              How to Build an Omnichannel Growth Engine in 60 Days
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              A step-by-step guide to creating a unified omnichannel marketing strategy that drives consistent growth across all customer touchpoints.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1460574283810-2aab119d8511?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Omnichannel growth strategy"
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
              Most businesses treat marketing channels as separate entities—email here, social media there, content marketing somewhere else. But customers don't experience your brand in silos. They expect a seamless, consistent experience across every touchpoint. Building an omnichannel growth engine isn't just nice to have—it's essential for competitive advantage.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The 60-Day Omnichannel Roadmap</h2>
            
            <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-blue-800">Timeline Overview</h4>
              <div className="grid md:grid-cols-3 gap-4">
                <div>
                  <div className="font-semibold text-blue-700">Days 1-20: Foundation</div>
                  <div className="text-sm text-blue-600">Audit, Strategy, Setup</div>
                </div>
                <div>
                  <div className="font-semibold text-blue-700">Days 21-40: Integration</div>
                  <div className="text-sm text-blue-600">Connect, Automate, Test</div>
                </div>
                <div>
                  <div className="font-semibold text-blue-700">Days 41-60: Optimization</div>
                  <div className="text-sm text-blue-600">Measure, Refine, Scale</div>
                </div>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Phase 1: Foundation (Days 1-20)</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Week 1: Complete Channel Audit</h3>
            
            <p className="mb-6">
              Start by mapping every customer touchpoint you currently have:
            </p>
            
            <ul className="list-disc pl-6 mb-6 space-y-2">
              <li>Website and landing pages</li>
              <li>Email marketing systems</li>
              <li>Social media platforms</li>
              <li>Paid advertising channels</li>
              <li>Customer service touchpoints</li>
              <li>Physical locations (if applicable)</li>
              <li>Mobile app interactions</li>
              <li>SMS/WhatsApp communication</li>
            </ul>
            
            <h3 className="text-2xl font-semibold mb-4">Week 2: Data Integration Planning</h3>
            
            <p className="mb-6">
              Identify how customer data flows between systems and where gaps exist:
            </p>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <Card className="border-t-4 border-t-red-500">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold text-red-700 mb-4">Common Data Silos</h4>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Email platform isolated from CRM</li>
                    <li>• Social media data not tracked</li>
                    <li>• Website behavior disconnected</li>
                    <li>• Customer service tickets separate</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold text-green-700 mb-4">Integration Goals</h4>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Single customer view across channels</li>
                    <li>• Unified messaging and timing</li>
                    <li>• Cross-channel attribution</li>
                    <li>• Automated trigger campaigns</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h3 className="text-2xl font-semibold mb-4">Week 3: Technology Stack Selection</h3>
            
            <p className="mb-6">
              Choose platforms that support omnichannel integration:
            </p>
            
            <div className="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-purple-800">Essential Integration Capabilities</h4>
              <ul className="space-y-2 text-purple-700">
                <li>• Real-time data synchronization</li>
                <li>• Cross-platform customer tracking</li>
                <li>• Unified campaign management</li>
                <li>• Advanced segmentation and personalization</li>
                <li>• Comprehensive analytics and reporting</li>
              </ul>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Phase 2: Integration (Days 21-40)</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Week 4-5: Technical Implementation</h3>
            
            <p className="mb-6">
              Connect your systems and establish data flows:
            </p>
            
            <ol className="list-decimal pl-6 mb-8 space-y-3">
              <li><strong>Implement tracking pixels</strong> across all channels</li>
              <li><strong>Set up API connections</strong> between platforms</li>
              <li><strong>Create unified customer profiles</strong> with all touchpoint data</li>
              <li><strong>Establish trigger-based automation</strong> between channels</li>
              <li><strong>Test data synchronization</strong> with sample campaigns</li>
            </ol>
            
            <h3 className="text-2xl font-semibold mb-4">Week 6: Content and Messaging Alignment</h3>
            
            <p className="mb-6">
              Ensure consistent brand voice and messaging across all channels:
            </p>
            
            <ul className="list-disc pl-6 mb-6 space-y-2">
              <li>Create unified brand guidelines</li>
              <li>Develop channel-specific content templates</li>
              <li>Establish cross-channel campaign workflows</li>
              <li>Set up automated messaging sequences</li>
            </ul>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Phase 3: Optimization (Days 41-60)</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Week 7-8: Launch and Monitor</h3>
            
            <p className="mb-6">
              Start with pilot campaigns and gradually scale:
            </p>
            
            <div className="grid md:grid-cols-3 gap-6 my-8">
              <Card className="border-t-4 border-t-yellow-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Target className="h-6 w-6 text-yellow-600 mr-2" />
                    <h4 className="text-lg font-bold text-yellow-700">Week 7</h4>
                  </div>
                  <ul className="text-sm space-y-1 text-gray-700">
                    <li>• Launch pilot campaigns</li>
                    <li>• Monitor data integration</li>
                    <li>• Test automation workflows</li>
                    <li>• Gather initial feedback</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-blue-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Layers className="h-6 w-6 text-blue-600 mr-2" />
                    <h4 className="text-lg font-bold text-blue-700">Week 8</h4>
                  </div>
                  <ul className="text-sm space-y-1 text-gray-700">
                    <li>• Analyze performance data</li>
                    <li>• Optimize weak points</li>
                    <li>• Scale successful elements</li>
                    <li>• Refine targeting</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <CheckCircle className="h-6 w-6 text-green-600 mr-2" />
                    <h4 className="text-lg font-bold text-green-700">Week 9+</h4>
                  </div>
                  <ul className="text-sm space-y-1 text-gray-700">
                    <li>• Full omnichannel rollout</li>
                    <li>• Advanced personalization</li>
                    <li>• Predictive optimization</li>
                    <li>• Continuous improvement</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Key Success Metrics to Track</h2>
            
            <div className="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-green-800">Omnichannel KPIs</h4>
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <div className="font-semibold text-green-700">Customer Experience</div>
                  <ul className="text-sm text-green-600 mt-2 space-y-1">
                    <li>• Cross-channel conversion rate</li>
                    <li>• Customer journey completion</li>
                    <li>• Channel attribution accuracy</li>
                    <li>• Message consistency score</li>
                  </ul>
                </div>
                <div>
                  <div className="font-semibold text-green-700">Business Impact</div>
                  <ul className="text-sm text-green-600 mt-2 space-y-1">
                    <li>• Customer lifetime value</li>
                    <li>• Average order value</li>
                    <li>• Customer retention rate</li>
                    <li>• Marketing efficiency ratio</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "Companies with strong omnichannel customer engagement see a 9.5% year-over-year increase in annual revenue compared to 3.4% for weak omnichannel companies." - Aberdeen Group
            </blockquote>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Common Pitfalls to Avoid</h2>
            
            <ul className="list-disc pl-6 mb-8 space-y-3">
              <li><strong>Trying to do everything at once:</strong> Start with 2-3 key channels and expand gradually</li>
              <li><strong>Ignoring data quality:</strong> Clean, accurate data is essential for omnichannel success</li>
              <li><strong>Focusing on technology over strategy:</strong> Tools are enablers, not solutions</li>
              <li><strong>Neglecting team training:</strong> Ensure your team understands the new workflows</li>
              <li><strong>Setting unrealistic timelines:</strong> Allow time for testing and optimization</li>
            </ul>
            
            <p className="text-lg font-medium mb-8">
              Building an omnichannel growth engine in 60 days is ambitious but achievable with focused execution and the right strategy. The key is starting with a solid foundation and building systematically.
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
                <h3 className="text-2xl font-bold mb-4">Ready to Build Your Omnichannel Engine?</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Get expert guidance to implement your 60-day omnichannel strategy
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <Globe className="h-5 w-5 mr-2" />
                      Analyze Current Channels
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-black bg-white hover:bg-gray-100 px-8 py-3">
                      Get Strategy Consultation
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

export default OmnichannelGrowthEngine;
