
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { 
  BarChart3, 
  Rocket, 
  BrainCircuit, 
  TrendingUp,
  CheckCircle,
  ArrowRight,
  Zap,
  Target,
  Users,
  MessageCircle
} from "lucide-react";

const Services = () => {
  const mainServices = [
    {
      icon: BarChart3,
      title: "Strategic Consulting",
      description: "Comprehensive business growth strategies tailored to your unique market position and goals.",
      image: "/lovable-uploads/c2f699f1-a432-4f51-956f-3a004df75a1c.png",
      alt: "Strategic business consulting session in Rotterdam",
      features: [
        "Market analysis and competitive research",
        "Business model optimization",
        "Growth strategy development",
        "Revenue stream diversification",
        "Performance benchmarking",
        "Strategic roadmap creation"
      ],
      price: "From €2,500/month"
    },
    {
      icon: Rocket,
      title: "Performance Marketing",
      description: "Data-driven marketing campaigns that deliver measurable results and maximize your ROI.",
      image: "/lovable-uploads/64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6.png",
      alt: "Performance marketing team working on campaigns",
      features: [
        "Multi-channel campaign management",
        "Conversion rate optimization",
        "Customer acquisition strategies",
        "Retargeting and remarketing",
        "A/B testing and optimization",
        "Performance analytics and reporting"
      ],
      price: "From €3,000/month"
    },
    {
      icon: BrainCircuit,
      title: "AI-Powered Solutions",
      description: "Leverage cutting-edge artificial intelligence to optimize your business operations and decision-making.",
      image: "/lovable-uploads/ccf94e11-7a43-4518-b095-62b335e3c4d7.png",
      alt: "AI technology implementation and optimization",
      features: [
        "Predictive analytics implementation",
        "Automated workflow optimization",
        "Customer behavior analysis",
        "AI-driven personalization",
        "Machine learning model development",
        "Data-driven decision support"
      ],
      price: "From €4,000/month"
    },
    {
      icon: TrendingUp,
      title: "Growth Optimization",
      description: "Comprehensive programs to scale your business efficiently and sustainably across all channels.",
      image: "/lovable-uploads/12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4.png",
      alt: "Growth optimization and business scaling",
      features: [
        "Scalable growth framework development",
        "Process automation and optimization",
        "Cross-functional team alignment",
        "Customer lifetime value optimization",
        "Retention strategy development",
        "Long-term growth planning"
      ],
      price: "From €3,500/month"
    }
  ];

  const additionalServices = [
    {
      icon: Zap,
      title: "Quick Wins Audit",
      description: "2-week intensive audit to identify immediate optimization opportunities",
      price: "€1,500 one-time"
    },
    {
      icon: Target,
      title: "Conversion Optimization",
      description: "Dedicated focus on improving your website and funnel conversion rates",
      price: "From €2,000/month"
    },
    {
      icon: Users,
      title: "Team Training",
      description: "Upskill your team with modern marketing and AI implementation workshops",
      price: "€500/session"
    },
    {
      icon: MessageCircle,
      title: "Monthly Strategy Calls",
      description: "Regular strategic guidance and performance review sessions",
      price: "€300/call"
    }
  ];

  return (
    <div className="min-h-screen bg-white">
      <Navbar />
      
      <main className="pt-20">
        {/* Hero Section */}
        <section className="py-16 md:py-24 bg-gradient-to-b from-venture-50 to-white">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center max-w-4xl mx-auto mb-16">
              <h1 className="text-4xl md:text-6xl font-bold mb-6">
                Our <span className="gradient-text">Services</span>
              </h1>
              <p className="text-xl text-foreground/80 leading-relaxed">
                Comprehensive solutions designed to accelerate your business growth through 
                innovative marketing strategies, AI-powered optimization, and strategic consulting.
              </p>
            </div>
          </div>
        </section>

        {/* Main Services */}
        <section className="py-16 md:py-24">
          <div className="container mx-auto px-4 md:px-6">
            <div className="space-y-16">
              {mainServices.map((service, index) => (
                <div key={index} className={`grid grid-cols-1 lg:grid-cols-2 gap-12 items-center ${index % 2 === 1 ? 'lg:grid-flow-col-dense' : ''}`}>
                  <div className={index % 2 === 1 ? 'lg:col-start-2' : ''}>
                    <div className="flex items-center mb-6">
                      <div className="h-12 w-12 rounded-lg bg-gradient-to-br from-booming-100 to-venture-100 flex items-center justify-center mr-4">
                        <service.icon className="h-6 w-6 text-booming-600" />
                      </div>
                      <div>
                        <h2 className="text-3xl font-bold">{service.title}</h2>
                        <p className="text-booming-600 font-semibold">{service.price}</p>
                      </div>
                    </div>
                    
                    <p className="text-lg text-foreground/80 mb-6">{service.description}</p>
                    
                    <div className="space-y-3 mb-8">
                      {service.features.map((feature, featureIndex) => (
                        <div key={featureIndex} className="flex items-start">
                          <CheckCircle className="h-5 w-5 text-venture-500 mr-3 mt-0.5 flex-shrink-0" />
                          <span>{feature}</span>
                        </div>
                      ))}
                    </div>
                    
                    <Button 
                      className="bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700"
                      onClick={() => window.location.href = "/#contact"}
                    >
                      Get Started
                      <ArrowRight className="ml-2 h-4 w-4" />
                    </Button>
                  </div>
                  
                  <div className={index % 2 === 1 ? 'lg:col-start-1' : ''}>
                    <img 
                      src={service.image}
                      alt={service.alt}
                      className="w-full h-80 object-cover rounded-lg shadow-lg"
                    />
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Additional Services */}
        <section className="py-16 md:py-24 bg-booming-50">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">Additional Services</h2>
              <p className="text-xl text-foreground/70">
                Flexible solutions to complement your growth strategy
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              {additionalServices.map((service, index) => (
                <Card key={index} className="border border-border h-full">
                  <CardHeader className="pb-4">
                    <div className="h-10 w-10 rounded-lg bg-gradient-to-br from-venture-100 to-booming-100 flex items-center justify-center mb-3">
                      <service.icon className="h-5 w-5 text-venture-600" />
                    </div>
                    <CardTitle className="text-lg">{service.title}</CardTitle>
                    <div className="text-booming-600 font-semibold text-sm">{service.price}</div>
                  </CardHeader>
                  <CardContent>
                    <CardDescription className="text-foreground/70">
                      {service.description}
                    </CardDescription>
                  </CardContent>
                </Card>
              ))}
            </div>
          </div>
        </section>

        {/* Process Section */}
        <section className="py-16 md:py-24">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Process</h2>
              <p className="text-xl text-foreground/70 max-w-3xl mx-auto">
                We follow a proven methodology to ensure your success, from initial 
                consultation to ongoing optimization and support.
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {[
                {
                  step: "01",
                  title: "Discovery Call",
                  description: "We start with a comprehensive consultation to understand your business, goals, and challenges."
                },
                {
                  step: "02",
                  title: "Strategy Design",
                  description: "Our team creates a customized strategy based on your specific needs and market opportunities."
                },
                {
                  step: "03",
                  title: "Implementation",
                  description: "We execute the strategy with precision, keeping you informed throughout the process."
                },
                {
                  step: "04",
                  title: "Optimization",
                  description: "Continuous monitoring and optimization to ensure maximum ROI and sustainable growth."
                }
              ].map((phase, index) => (
                <div key={index} className="text-center">
                  <div className="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-booming-600 to-venture-600 text-white rounded-full text-xl font-bold mb-4">
                    {phase.step}
                  </div>
                  <h3 className="text-xl font-semibold mb-3">{phase.title}</h3>
                  <p className="text-foreground/70">{phase.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Free Tools Section */}
        <section className="py-16 md:py-24 bg-venture-50">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">Free Business Tools</h2>
              <p className="text-xl text-foreground/70">
                Try our powerful calculators to understand your growth potential
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
              <Card className="border-2 border-venture-200 hover:border-venture-400 transition-colors">
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <BarChart3 className="h-6 w-6 text-venture-600 mr-3" />
                    Funnel Leak Calculator
                  </CardTitle>
                  <CardDescription>
                    Discover how much revenue you're losing due to funnel inefficiencies
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Button 
                    className="w-full bg-venture-600 hover:bg-venture-700"
                    onClick={() => window.location.href = "/funnel-calculator"}
                  >
                    Try Now - Free
                  </Button>
                </CardContent>
              </Card>
              
              <Card className="border-2 border-booming-200 hover:border-booming-400 transition-colors">
                <CardHeader>
                  <CardTitle className="flex items-center">
                    <TrendingUp className="h-6 w-6 text-booming-600 mr-3" />
                    ROI Forecaster
                  </CardTitle>
                  <CardDescription>
                    Predict your marketing ROI and optimize your budget allocation
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <Button 
                    className="w-full bg-booming-600 hover:bg-booming-700"
                    onClick={() => window.location.href = "/roi-forecaster"}
                  >
                    Try Now - Free
                  </Button>
                </CardContent>
              </Card>
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-16 md:py-24 bg-gradient-to-r from-venture-600 to-booming-600">
          <div className="container mx-auto px-4 md:px-6 text-center">
            <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
              Ready to Accelerate Your Growth?
            </h2>
            <p className="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
              Let's discuss which service combination will deliver the best results for your business. 
              Schedule a free consultation with our Rotterdam-based team.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <Button 
                size="lg" 
                className="bg-white text-venture-600 hover:bg-gray-100 font-semibold px-8 py-6 h-auto"
                onClick={() => window.location.href = "/#contact"}
              >
                Schedule Free Consultation
              </Button>
              <Button 
                size="lg" 
                variant="outline"
                className="border-white text-black hover:bg-white hover:text-venture-600 font-semibold px-8 py-6 h-auto bg-white"
                onClick={() => window.location.href = "mailto:info@boomingventure.com"}
              >
                Email Us Directly
              </Button>
            </div>
          </div>
        </section>
      </main>
      
      <Footer />
    </div>
  );
};

export default Services;
