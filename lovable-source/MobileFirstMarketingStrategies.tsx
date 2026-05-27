import { useEffect } from "react";
import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { CalendarDays, Clock, ArrowLeft, Smartphone, Target, TrendingUp } from "lucide-react";

const MobileFirstMarketingStrategies = () => {
  useEffect(() => {
    document.title = "Mobile-First Marketing Strategies for 2024 | Booming Venture";
    
    const structuredData = {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": "Mobile-first marketing strategies for 2024",
      "description": "With mobile accounting for 60%+ of web traffic, your marketing strategy must be mobile-first. Learn the tactics that drive mobile conversions.",
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
      "datePublished": "2024-02-07",
      "dateModified": "2024-02-07",
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://boomingventure.com/blog/mobile-first-marketing-strategies-2024"
      }
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
                Mobile Marketing
              </Badge>
              <div className="flex items-center text-sm text-muted-foreground">
                <CalendarDays className="h-4 w-4 mr-1" />
                February 7, 2024
              </div>
              <div className="flex items-center text-sm text-muted-foreground">
                <Clock className="h-4 w-4 mr-1" />
                8 min read
              </div>
            </div>
            
            <h1 className="text-4xl md:text-5xl font-bold mb-6 leading-tight">
              Mobile-first marketing strategies for 2024
            </h1>
            
            <p className="text-xl text-muted-foreground leading-relaxed">
              With mobile accounting for 60%+ of web traffic, your marketing strategy must be mobile-first. Learn the tactics that drive mobile conversions.
            </p>
          </motion.header>
          
          <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: 0.2 }}
            className="mb-12"
          >
            <img 
              src="https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80"
              alt="Mobile marketing strategies"
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
              Mobile isn't the future of marketing—it's the present. With over 60% of web traffic now coming from mobile devices, and mobile commerce growing 15% year-over-year, businesses that haven't adopted a mobile-first approach are already behind. But mobile-first marketing goes beyond responsive design; it's about fundamentally rethinking how you engage with customers.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Mobile Mindset Shift</h2>
            
            <p className="mb-6">
              Mobile users behave differently than desktop users. They're often multitasking, have shorter attention spans, and expect instant gratification. Your marketing strategy needs to account for these behavioral differences, not just adapt your desktop strategy to a smaller screen.
            </p>
            
            <Card className="my-8 border-l-4 border-l-blue-500">
              <CardContent className="p-6">
                <h3 className="text-xl font-bold mb-4 text-blue-700">Mobile User Psychology</h3>
                <ul className="space-y-2 text-gray-700">
                  <li>• 3-second attention span for content consumption</li>
                  <li>• 70% prefer one-thumb navigation</li>
                  <li>• 88% abandon slow-loading mobile sites</li>
                  <li>• 52% of users say slow mobile experiences make them less likely to engage with a company</li>
                </ul>
              </CardContent>
            </Card>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Mobile-First Content Strategy</h2>
            
            <h3 className="text-2xl font-semibold mb-4">1. Micro-Moments Marketing</h3>
            
            <p className="mb-6">
              Mobile users exist in a world of micro-moments—brief instances when they turn to their device with specific intent. Your content strategy should anticipate and serve these moments with precision.
            </p>
            
            <div className="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4 text-green-800">The Four Micro-Moments:</h4>
              <ul className="space-y-3 text-green-700">
                <li className="flex items-start gap-3">
                  <Target className="h-5 w-5 mt-1 text-green-600" />
                  <span><strong>I-want-to-know:</strong> Quick answers and information</span>
                </li>
                <li className="flex items-start gap-3">
                  <Target className="h-5 w-5 mt-1 text-green-600" />
                  <span><strong>I-want-to-go:</strong> Local search and directions</span>
                </li>
                <li className="flex items-start gap-3">
                  <Target className="h-5 w-5 mt-1 text-green-600" />
                  <span><strong>I-want-to-do:</strong> How-to content and tutorials</span>
                </li>
                <li className="flex items-start gap-3">
                  <Target className="h-5 w-5 mt-1 text-green-600" />
                  <span><strong>I-want-to-buy:</strong> Product information and purchase options</span>
                </li>
              </ul>
            </div>
            
            <h3 className="text-2xl font-semibold mb-4">2. Vertical Video Dominance</h3>
            
            <p className="mb-6">
              Vertical video isn't just a trend—it's the native format for mobile consumption. Brands creating vertical-first video content see 9x higher completion rates compared to horizontal video on mobile devices.
            </p>
            
            <h3 className="text-2xl font-semibold mb-4">3. Voice Search Optimization</h3>
            
            <p className="mb-6">
              With 55% of teens and 41% of adults using voice search daily, optimizing for conversational queries is no longer optional. Mobile voice searches are 3x more likely to be local-based.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Mobile Conversion Optimization</h2>
            
            <div className="grid md:grid-cols-2 gap-6 my-8">
              <Card className="border-t-4 border-t-purple-500">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold mb-4 text-purple-700">Speed Optimization</h4>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Target sub-3 second load times</li>
                    <li>• Implement AMP for content pages</li>
                    <li>• Optimize images for mobile bandwidth</li>
                    <li>• Use progressive web app features</li>
                  </ul>
                </CardContent>
              </Card>
              
              <Card className="border-t-4 border-t-orange-500">
                <CardContent className="p-6">
                  <h4 className="text-xl font-bold mb-4 text-orange-700">Touch-First UX</h4>
                  <ul className="space-y-2 text-gray-700">
                    <li>• Minimum 44px touch targets</li>
                    <li>• Thumb-zone navigation design</li>
                    <li>• Swipe gestures for interactions</li>
                    <li>• One-handed operation priority</li>
                  </ul>
                </CardContent>
              </Card>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Mobile-First Advertising Tactics</h2>
            
            <h3 className="text-2xl font-semibold mb-4">Social Stories and Short-Form Video</h3>
            
            <p className="mb-6">
              Stories format generates 15-25% higher reach than traditional feed posts. Create content specifically for the 9:16 aspect ratio and design for sound-off viewing with captions and visual storytelling.
            </p>
            
            <blockquote className="border-l-4 border-booming-500 pl-6 my-8 text-xl italic text-gray-700">
              "Our mobile conversion rate increased by 47% when we redesigned our entire customer journey around mobile-first principles, not just responsive design." - Dutch E-commerce Director
            </blockquote>
            
            <h3 className="text-2xl font-semibold mb-4">Location-Based Marketing</h3>
            
            <p className="mb-6">
              Mobile devices provide unprecedented location data opportunities. Geofencing, location-triggered push notifications, and local search optimization create hyper-relevant customer experiences.
            </p>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Mobile Analytics and Measurement</h2>
            
            <p className="mb-6">
              Traditional web analytics don't capture the full mobile experience. Focus on mobile-specific metrics that correlate with business outcomes.
            </p>
            
            <div className="bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg p-6 mb-8">
              <h4 className="text-xl font-bold mb-4">Essential Mobile Metrics:</h4>
              <div className="grid md:grid-cols-2 gap-4">
                <div>
                  <strong className="text-blue-700">Performance Metrics</strong>
                  <ul className="text-sm text-gray-600 mt-2">
                    <li>• Page load speed on 3G</li>
                    <li>• First meaningful paint time</li>
                    <li>• App store conversion rate</li>
                  </ul>
                </div>
                <div>
                  <strong className="text-blue-700">Engagement Metrics</strong>
                  <ul className="text-sm text-gray-600 mt-2">
                    <li>• Scroll depth on mobile</li>
                    <li>• Touch heatmaps</li>
                    <li>• Session duration by device</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">The Mobile-First Future</h2>
            
            <p className="mb-6">
              Mobile-first isn't just about adapting to current user behavior—it's about preparing for the future. With 5G networks, augmented reality, and progressive web apps, mobile experiences will only become more immersive and important.
            </p>
            
            <div className="grid md:grid-cols-3 gap-4 my-8">
              <div className="text-center p-4 bg-blue-50 rounded-lg">
                <div className="text-2xl font-bold text-blue-600 mb-2">73%</div>
                <div className="text-sm text-gray-600">of mobile users abandon sites that take longer than 3 seconds to load</div>
              </div>
              <div className="text-center p-4 bg-green-50 rounded-lg">
                <div className="text-2xl font-bold text-green-600 mb-2">6x</div>
                <div className="text-sm text-gray-600">higher engagement rates for mobile-optimized content</div>
              </div>
              <div className="text-center p-4 bg-purple-50 rounded-lg">
                <div className="text-2xl font-bold text-purple-600 mb-2">61%</div>
                <div className="text-sm text-gray-600">of users unlikely to return to a mobile site they had trouble accessing</div>
              </div>
            </div>
            
            <h2 className="text-3xl font-bold mb-6 mt-12">Your Mobile-First Action Plan</h2>
            
            <p className="mb-6">
              The shift to mobile-first marketing isn't optional—it's essential for survival in today's digital landscape. Start with your highest-impact touchpoints and gradually optimize your entire customer journey for mobile.
            </p>
            
            <p className="text-lg font-medium mb-8">
              Remember: mobile-first doesn't mean mobile-only. It means designing for mobile and scaling up, ensuring every interaction is optimized for the device most of your customers are using.
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
                <h3 className="text-2xl font-bold mb-4">Ready to Go Mobile-First?</h3>
                <p className="text-lg mb-6 text-blue-100">
                  Get expert help optimizing your mobile marketing strategy for maximum conversions
                </p>
                <div className="flex flex-col sm:flex-row gap-4 justify-center">
                  <Link to="/funnel-calculator">
                    <Button className="bg-white text-booming-600 hover:bg-gray-100 px-8 py-3">
                      <Smartphone className="h-5 w-5 mr-2" />
                      Analyze My Funnel
                    </Button>
                  </Link>
                  <Link to="/#contact">
                    <Button variant="outline" className="border-white text-black bg-white hover:bg-gray-100 px-8 py-3">
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

export default MobileFirstMarketingStrategies;
