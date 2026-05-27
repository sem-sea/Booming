
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";
import { Button } from "@/components/ui/button";
import { CheckCircle2, Users, Target, Trophy, Heart } from "lucide-react";

const About = () => {
  const aboutPageSchema = {
    "@context": "https://schema.org",
    "@type": "AboutPage",
    "name": "About Booming Venture",
    "description": "Learn about Booming Venture - Rotterdam-based marketing consultancy combining strategic thinking with cutting-edge AI technology",
    "url": "https://boomingventure.com/about",
    "mainEntity": {
      "@type": "Organization",
      "name": "Booming Venture",
      "description": "Rotterdam-based AI marketing implementation agency that combines strategic thinking with cutting-edge AI technology to help businesses achieve exceptional growth",
      "foundingDate": "2024",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Rotterdam",
        "addressCountry": "Netherlands"
      }
    }
  };

  return (
    <div className="min-h-screen bg-white">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(aboutPageSchema) }}
      />
      
      <Navbar />
      
      <main className="pt-20">
        {/* Hero Section */}
        <section className="py-16 md:py-24 bg-gradient-to-b from-booming-50 to-white">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center max-w-4xl mx-auto mb-16">
              <h1 className="text-4xl md:text-6xl font-bold mb-6">
                About <span className="gradient-text">Booming Venture</span>
              </h1>
              <p className="text-xl text-foreground/80 leading-relaxed">
                We're a Rotterdam-based AI marketing agency that combines strategic thinking 
                with cutting-edge AI technology to help businesses achieve exceptional growth 
                in today's competitive marketplace.
              </p>
            </div>
          </div>
        </section>

        {/* Our Story */}
        <section className="py-16 md:py-24">
          <div className="container mx-auto px-4 md:px-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              <div>
                <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Story</h2>
                <p className="text-lg text-foreground/80 mb-6">
                  Founded in Rotterdam, Booming Venture emerged from a simple belief:
Every business deserves access to world-class marketing strategies and AI-powered solutions — no matter their size or industry.

They deserve bold, data-driven strategies that scale fast.
At Booming Venture, we help brands do exactly that: boom.
                </p>
                <p className="text-lg text-foreground/80 mb-6">
                  Our team of experts combines decades of experience in business growth, 
                  marketing optimization, and AI implementation to deliver measurable 
                  results for our clients across the Netherlands and beyond.
                </p>
                <p className="text-lg text-foreground/80">
                  We don’t just provide AI services — we act as your strategic partner, working alongside your team to unlock the full growth potential of your business.
                </p>
              </div>
              <div className="relative">
                <img 
                  src="/lovable-uploads/4e357139-5a7e-4336-8796-94013f33dc3d.png" 
                  alt="Booming Venture team collaborating in Rotterdam office"
                  className="w-full rounded-lg shadow-lg"
                />
              </div>
            </div>
          </div>
        </section>

        {/* Our Values */}
        <section className="py-16 md:py-24 bg-booming-50">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Core Values</h2>
              <p className="text-xl text-foreground/70">
                The principles that guide everything we do
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
              {[
                {
                  icon: Target,
                  title: "Results-Driven",
                  description: "We focus on delivering measurable outcomes with clear KPIs and performance metrics."
                },
                {
                  icon: Heart,
                  title: "Client-Centric",
                  description: "Your success is our success. We build long-term partnerships based on trust and transparency."
                },
                {
                  icon: Trophy,
                  title: "Innovation",
                  description: "We stay ahead of the curve, leveraging the latest AI technologies and marketing trends."
                },
                {
                  icon: Users,
                  title: "Collaboration",
                  description: "We work as an extension of your team, bringing expertise while respecting your vision."
                }
              ].map((value, index) => (
                <div key={index} className="bg-white p-6 rounded-lg shadow-sm text-center">
                  <div className="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-booming-100 to-venture-100 rounded-lg mb-4">
                    <value.icon className="h-6 w-6 text-booming-600" />
                  </div>
                  <h3 className="text-xl font-semibold mb-3">{value.title}</h3>
                  <p className="text-foreground/70">{value.description}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Our Approach */}
        <section className="py-16 md:py-24">
          <div className="container mx-auto px-4 md:px-6">
            <div className="text-center mb-16">
              <h2 className="text-3xl md:text-4xl font-bold mb-6">Our Approach</h2>
              <p className="text-xl text-foreground/70 max-w-3xl mx-auto">
                We believe in a data-driven, personalized approach to business growth. 
                Here's how we work with our clients to achieve exceptional results.
              </p>
            </div>
            
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {[
                {
                  step: "01",
                  title: "Analyze & Understand",
                  description: "We start by deeply understanding your business, market position, and growth challenges through comprehensive analysis."
                },
                {
                  step: "02",
                  title: "Strategize & Plan",
                  description: "Based on our findings, we develop a customized strategy that aligns with your goals and leverages AI-powered insights."
                },
                {
                  step: "03",
                  title: "Implement & Optimize",
                  description: "We execute the strategy with precision, continuously monitoring performance and optimizing for maximum ROI."
                }
              ].map((phase, index) => (
                <div key={index} className="relative">
                  <div className="bg-gradient-to-br from-booming-100 to-venture-100 p-8 rounded-lg h-full">
                    <div className="text-4xl font-bold text-booming-600 mb-4">{phase.step}</div>
                    <h3 className="text-xl font-semibold mb-4">{phase.title}</h3>
                    <p className="text-foreground/70">{phase.description}</p>
                  </div>
                  {index < 2 && (
                    <div className="hidden md:block absolute top-1/2 -right-4 transform -translate-y-1/2">
                      <div className="w-8 h-0.5 bg-gradient-to-r from-booming-300 to-venture-300"></div>
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* Why Choose Us */}
        <section className="py-16 md:py-24 bg-venture-50">
          <div className="container mx-auto px-4 md:px-6">
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              <div>
                <h2 className="text-3xl md:text-4xl font-bold mb-6">
                  Why Choose Booming Venture?
                </h2>
                <div className="space-y-4">
                  {[
                    "Proven track record with 200+ successful projects",
                    "AI-powered strategies that deliver 3x better results",
                    "Rotterdam-based team with global expertise",
                    "Transparent communication and regular reporting",
                    "Custom solutions tailored to your industry",
                    "Ongoing support and optimization"
                  ].map((benefit, index) => (
                    <div key={index} className="flex items-start">
                      <CheckCircle2 className="h-6 w-6 text-venture-600 mr-3 mt-0.5 flex-shrink-0" />
                      <span className="text-lg">{benefit}</span>
                    </div>
                  ))}
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <img 
                  src="/lovable-uploads/12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4.png" 
                  alt="Team celebrating success in Rotterdam"
                  className="w-full h-48 object-cover rounded-lg"
                />
                <img 
                  src="/lovable-uploads/64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6.png" 
                  alt="Data analytics and AI optimization"
                  className="w-full h-48 object-cover rounded-lg mt-8"
                />
                <img 
                  src="/lovable-uploads/c2f699f1-a432-4f51-956f-3a004df75a1c.png" 
                  alt="Strategic planning session"
                  className="w-full h-48 object-cover rounded-lg -mt-8"
                />
                <img 
                  src="/lovable-uploads/ccf94e11-7a43-4518-b095-62b335e3c4d7.png" 
                  alt="AI technology implementation"
                  className="w-full h-48 object-cover rounded-lg"
                />
              </div>
            </div>
          </div>
        </section>

        {/* CTA Section */}
        <section className="py-16 md:py-24 bg-gradient-to-r from-booming-600 to-venture-600">
          <div className="container mx-auto px-4 md:px-6 text-center">
            <h2 className="text-3xl md:text-4xl font-bold text-white mb-6">
              Ready to Grow Your Business?
            </h2>
            <p className="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
              Let's discuss how we can help you achieve your growth goals with our 
              proven strategies and AI-powered solutions.
            </p>
            <Button 
              size="lg" 
              className="bg-white text-booming-600 hover:bg-gray-100 font-semibold px-8 py-6 h-auto"
              onClick={() => window.location.href = "/#contact"}
            >
              Schedule Your Free Consultation
            </Button>
          </div>
        </section>
      </main>
      
      <Footer />
    </div>
  );
};

export default About;
