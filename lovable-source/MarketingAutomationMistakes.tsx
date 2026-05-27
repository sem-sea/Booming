
import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, AlertTriangle, CheckCircle, XCircle } from "lucide-react";

const MarketingAutomationMistakes = () => {
  useEffect(() => {
    document.title = "5 Marketing Automation Mistakes That Are Costing You Customers | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "5 marketing automation mistakes that are costing you customers",
      "description": "Marketing automation can be a game-changer or a customer repellent. Learn the critical mistakes that turn prospects away and how to fix them.",
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
      "datePublished": "2024-01-24",
      "dateModified": "2024-01-24"
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
              <Badge variant="secondary" className="bg-gradient-to-r from-red-500 to-orange-500 text-white">
                Marketing Automation
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                January 24, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                7 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              5 marketing automation mistakes that are costing you customers
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Marketing automation can be a game-changer or a customer repellent. Learn the critical mistakes that turn prospects away and how to fix them.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Marketing automation workflow visualization"
              className="w-full h-[400px] object-cover rounded-lg shadow-lg"
            />
          </motion.div>
          
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.4 }}
            className="prose prose-lg max-w-none"
          >
            <div className="bg-red-50 border border-red-200 rounded-lg p-6 mb-8">
              <div className="flex items-center gap-3 mb-4">
                <AlertTriangle className="h-6 w-6 text-red-600" />
                <h3 className="text-xl font-bold text-red-800 m-0">The Automation Paradox</h3>
              </div>
              <p className="text-red-700 m-0">
                67% of businesses use marketing automation, but only 23% see the results they expected. The difference? Avoiding these five critical mistakes that turn automation from an asset into a liability.
              </p>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Mistake #1: Over-Automation Without Human Touch</h2>
            
            <p className="mb-6">
              The biggest mistake Dutch SMEs make is automating everything without maintaining human connection. Your customers can tell when they're talking to a robot, and 73% of them don't like it.
            </p>
            
            <div className="grid md:grid-cols-2 gap-6 mb-8">
              <Card className="border-l-4 border-l-red-500">
                <CardContent className="p-6">
                  <div className="flex items-center gap-3 mb-4">
                    <XCircle className="h-6 w-6 text-red-600" />
                    <h4 className="text-lg font-bold text-red-700">What NOT to Do</h4>
                  </div>
                  <ul className="space-y-2 text-sm">
                    <li>• Automate every customer touchpoint</li>
                    <li>• Use generic, templated responses</li>
                    <li>• Never allow human intervention</li>
                    <li>• Ignore customer feedback about automation</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-l-4 border-l-green-500">
                <CardContent className="p-6">
                  <div className="flex items-center gap-3 mb-4">
                    <CheckCircle className="h-6 w-6 text-green-600" />
                    <h4 className="text-lg font-bold text-green-700">Best Practice</h4>
                  </div>
                  <ul className="space-y-2 text-sm">
                    <li>• Keep high-value interactions human</li>
                    <li>• Personalize automated messages</li>
                    <li>• Provide easy escalation to humans</li>
                    <li>• Monitor automation performance</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Mistake #2: Poor Segmentation Strategy</h2>
            
            <p className="mb-6">
              Sending the same automated sequence to all your contacts is like using a megaphone in a library—loud, annoying, and ineffective. Yet 58% of businesses still use basic demographic segmentation or worse, no segmentation at all.
            </p>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "We were sending product updates to customers who had already purchased, and promotional offers to people who had just bought. No wonder our unsubscribe rate was 12%." - Rotterdam Tech Startup
            </blockquote>
            
            <h3 className="text-2xl font-semibold mb-4">Advanced Segmentation Framework</h3>
            
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-blue-800">Behavioral Segmentation Categories:</h4>
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <strong className="text-blue-700">Purchase Behavior</strong>
                  <ul className="text-sm text-blue-600 mt-2">
                    <li>• First-time buyers</li>
                    <li>• Repeat customers</li>
                    <li>• High-value customers</li>
                    <li>• Price-sensitive buyers</li>
                  </ul>
                </div>
                <div>
                  <strong className="text-blue-700">Engagement Level</strong>
                  <ul className="text-sm text-blue-600 mt-2">
                    <li>• Highly engaged</li>
                    <li>• Moderately engaged</li>
                    <li>• At-risk/dormant</li>
                    <li>• Re-engagement candidates</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Mistake #3: Ignoring Mobile Experience</h2>
            
            <p className="mb-6">
              68% of email opens happen on mobile devices, yet many automated campaigns are still designed for desktop. This disconnect leads to poor user experience and lost conversions.
            </p>
            
            <h2 className="text-3xl font-bold mb-6">Mistake #4: Setting and Forgetting</h2>
            
            <p className="mb-6">
              Automation isn't "set it and forget it." Markets change, customer behavior evolves, and what worked six months ago might be driving customers away today. Regular optimization is crucial.
            </p>
            
            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-yellow-800">Monthly Automation Audit Checklist:</h4>
              <ul className="space-y-2 text-yellow-700">
                <li>✓ Review open and click-through rates</li>
                <li>✓ Analyze conversion metrics</li>
                <li>✓ Check unsubscribe rates</li>
                <li>✓ Test email deliverability</li>
                <li>✓ Update segmentation criteria</li>
                <li>✓ Refresh content and offers</li>
              </ul>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Mistake #5: Lack of Integration</h2>
            
            <p className="mb-6">
              Your marketing automation platform should talk to your CRM, your analytics tools, and your customer service system. Siloed automation creates inconsistent customer experiences and missed opportunities.
            </p>
            
            <h2 className="text-3xl font-bold mb-6">The Rotterdam Recovery Plan</h2>
            
            <p className="mb-6">
              If you recognize your business in these mistakes, don't panic. Here's a proven 30-day recovery plan we've used with Dutch SMEs:
            </p>
            
            <div className="grid md:grid-cols-3 gap-6 mb-8">
              <Card className="border-t-4 border-t-blue-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-blue-700">Week 1: Audit</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Review all active automations</li>
                    <li>• Analyze performance metrics</li>
                    <li>• Survey customers about experience</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-orange-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-orange-700">Week 2-3: Fix</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Improve segmentation</li>
                    <li>• Add human touchpoints</li>
                    <li>• Optimize for mobile</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-green-500">
                <CardContent className="p-6">
                  <h4 className="text-lg font-bold mb-4 text-green-700">Week 4: Monitor</h4>
                  <ul className="space-y-2 text-sm">
                    <li>• Track improved metrics</li>
                    <li>• Gather customer feedback</li>
                    <li>• Plan ongoing optimization</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6">Your Next Steps</h2>
            
            <p className="mb-6">
              Marketing automation should enhance your customer relationships, not replace them. By avoiding these five mistakes, you can transform your automation from a customer repellent into a powerful growth engine.
            </p>
            
            <p className="text-lg font-medium">
              Remember: The goal isn't to automate everything—it's to automate the right things in the right way.
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
                <h3 className="text-2xl font-bold mb-4">Fix Your Automation Strategy</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Get a free automation audit and discover what's driving your customers away
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
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

export default MarketingAutomationMistakes;
