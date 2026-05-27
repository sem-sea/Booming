
import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, Brain, Target, TrendingUp, Eye } from "lucide-react";

const ConversionOptimizationPsychology = () => {
  useEffect(() => {
    document.title = "The Psychology Behind High-Converting Landing Pages | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "The Psychology Behind High-Converting Landing Pages",
      "description": "Understanding user psychology is the key to conversion optimization. Discover the cognitive biases and behavioral triggers that drive purchasing decisions.",
      "author": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Booming Venture"
      },
      "datePublished": "2024-02-01",
      "dateModified": "2024-02-01"
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
                Conversion Optimization
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                February 1, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                12 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              The Psychology Behind High-Converting Landing Pages
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              Understanding user psychology is the key to conversion optimization. Discover the cognitive biases and behavioral triggers that drive purchasing decisions.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1559526324-593bc073d938?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Psychology behind conversion optimization - brain and user behavior"
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
              Great landing pages don't just look good—they tap into fundamental human psychology. By understanding how people think, decide, and act, you can create pages that convert visitors into customers with scientific precision.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Cognitive Biases That Drive Conversions</h2>
            
            <h3 className="text-2xl font-semibold mb-4">1. Social Proof (The Bandwagon Effect)</h3>
            <p className="mb-6">
              People look to others' behavior to guide their own decisions. When visitors see that others have chosen your product, they're more likely to do the same.
            </p>
            
            <div className="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-green-800">Implementation Examples</h4>
              <ul className="space-y-2 text-green-700">
                <li>• "Join 50,000+ satisfied customers"</li>
                <li>• Customer testimonials with photos</li>
                <li>• Real-time activity feeds ("John from Amsterdam just purchased...")</li>
                <li>• Trust badges from recognizable brands</li>
              </ul>
            </div>
            
            <h3 className="text-2xl font-semibold mb-4">2. Scarcity and Urgency (Loss Aversion)</h3>
            <p className="mb-6">
              People fear losing out more than they value gaining. Limited-time offers and stock notifications create urgency that drives immediate action.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">3. Authority Bias</h3>
            <p className="mb-6">
              We naturally defer to experts and authority figures. Showcasing credentials, certifications, and expert endorsements builds trust and credibility.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Psychology of Visual Hierarchy</h2>
            
            <div className="grid md:grid-cols-3 gap-6 my-8">
              <Card className="border-t-4 border-t-blue-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Eye className="h-6 w-6 text-blue-600 mr-2" />
                    <h4 className="text-lg font-bold text-blue-700">F-Pattern Reading</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    Users scan in an F-pattern. Place key information along these visual paths.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-purple-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Brain className="h-6 w-6 text-purple-600 mr-2" />
                    <h4 className="text-lg font-bold text-purple-700">Color Psychology</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    Colors evoke emotions. Red creates urgency, blue builds trust, green suggests success.
                  </p>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-orange-500">
                <CardContent className="p-6">
                  <div className="flex items-center mb-4">
                    <Target className="h-6 w-6 text-orange-600 mr-2" />
                    <h4 className="text-lg font-bold text-orange-700">Contrast & Focus</h4>
                  </div>
                  <p className="text-sm text-gray-700">
                    High contrast draws attention to CTAs. Use whitespace to reduce cognitive load.
                  </p>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Paradox of Choice</h2>
            
            <p className="mb-6">
              Too many options paralyze decision-making. Research shows that having 3 options leads to higher conversion rates than having 24 options.
            </p>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "We reduced our pricing plans from 6 to 3 options and saw a 47% increase in conversions. Sometimes less really is more." - SaaS Company CEO
            </blockquote>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Psychological Triggers in Copy</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Power Words That Convert</h3>
            <ul className="list-disc pl-6 mb-6 space-y-2">
              <li><strong>"Instantly"</strong> - Appeals to our desire for immediate gratification</li>
              <li><strong>"Exclusive"</strong> - Makes people feel special and part of an elite group</li>
              <li><strong>"Proven"</strong> - Reduces risk by implying testing and validation</li>
              <li><strong>"Limited"</strong> - Triggers scarcity and urgency</li>
              <li><strong>"Guaranteed"</strong> - Removes fear and risk from the decision</li>
            </ul>
            
            <h3 className="text-2xl font-semibold mb-4">The Psychology of Pricing</h3>
            <ul className="list-disc pl-6 mb-8 space-y-2">
              <li><strong>Charm Pricing (€19.99 vs €20)</strong> - The left digit bias makes prices seem lower</li>
              <li><strong>Anchoring</strong> - Show a higher-priced option first to make others seem reasonable</li>
              <li><strong>Bundling</strong> - People prefer packages over individual items</li>
              <li><strong>Decoy Effect</strong> - Include a deliberately inferior option to make your target option look better</li>
            </ul>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Emotional vs. Rational Decision Making</h2>
            
            <p className="mb-6">
              Research shows that people make decisions emotionally and then justify them rationally. Your landing page needs to trigger both:
            </p>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <div className="bg-red-50 border border-red-200 rounded-lg p-6">
                <h4 className="text-xl font-bold mb-4 text-red-800">Emotional Triggers</h4>
                <ul className="space-y-2 text-red-700">
                  <li>• Fear of missing out</li>
                  <li>• Desire for status</li>
                  <li>• Need for security</li>
                  <li>• Aspiration for success</li>
                </ul>
              </div>
              
              <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 className="text-xl font-bold mb-4 text-blue-800">Rational Justifications</h4>
                <ul className="space-y-2 text-blue-700">
                  <li>• Cost-benefit analysis</li>
                  <li>• Feature comparisons</li>
                  <li>• ROI calculations</li>
                  <li>• Risk assessments</li>
                </ul>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Testing Psychological Principles</h2>
            
            <ol className="list-decimal pl-6 mb-8 space-y-4">
              <li>
                <strong>Start with One Element</strong>
                <p className="text-gray-600 mt-2">Test one psychological principle at a time to isolate its impact.</p>
              </li>
              <li>
                <strong>Measure Behavior, Not Opinions</strong>
                <p className="text-gray-600 mt-2">Track actual conversions, not just what people say they prefer.</p>
              </li>
              <li>
                <strong>Consider Your Audience</strong>
                <p className="text-gray-600 mt-2">Different demographics respond to different psychological triggers.</p>
              </li>
              <li>
                <strong>Test Long-term Impact</strong>
                <p className="text-gray-600 mt-2">Some psychological tactics may increase short-term conversions but hurt long-term trust.</p>
              </li>
            </ol>
            
            <p className="text-lg font-medium mb-8">
              The most effective landing pages seamlessly blend psychological insights with genuine value. Use these principles ethically to help customers make decisions that truly benefit them.
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
                <h3 className="text-2xl font-bold mb-4">Ready to Apply Psychology to Your Pages?</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Let's optimize your landing pages using proven psychological principles
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <Brain className="h-5 w-5 mr-2" />
                      Analyze Your Pages
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-black bg-white hover:bg-gray-100 px-8 py-3">
                      Get Conversion Audit
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

export default ConversionOptimizationPsychology;
