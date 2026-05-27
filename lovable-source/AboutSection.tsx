
import { Button } from "@/components/ui/button";
import { CheckCircle2 } from "lucide-react";

const AboutSection = () => {
  return (
    <section id="about" className="py-16 md:py-24">
      <div className="container mx-auto px-4 md:px-6">
        <div className="flex flex-col lg:flex-row items-center gap-12">
          <div className="lg:w-1/2 animate-fade-in" style={{ animationDelay: "0.1s" }}>
            <div className="relative">
              <div className="absolute -left-4 -top-4 w-24 h-24 bg-booming-100 rounded-lg"></div>
              <div className="absolute -right-4 -bottom-4 w-24 h-24 bg-venture-100 rounded-lg"></div>
              <div className="relative bg-gradient-to-tr from-booming-600 to-venture-500 p-1 rounded-lg">
                <div className="bg-white p-8 rounded-md">
                  <div className="mb-6 rounded-lg overflow-hidden">
                    <img 
                      src="/lovable-uploads/4e357139-5a7e-4336-8796-94013f33dc3d.png" 
                      alt="Confident business leader giving presentation to diverse team in modern office"
                      className="w-full h-auto rounded-lg"
                    />
                  </div>
                  <h3 className="text-2xl font-bold mb-4">Our Mission</h3>
                  <p className="text-foreground/80 mb-6">
                    Based from Rotterdam, The Netherlands, we're committed to empowering businesses 
                    with innovative marketing strategies and AI-driven solutions that drive sustainable growth.
                  </p>
                  
                  <h4 className="text-xl font-semibold mb-3">Our Values</h4>
                  <ul className="space-y-2">
                    {[
                      "Innovation at the core of everything we do",
                      "Result-oriented approach to business growth",
                      "Transparency and integrity in all partnerships",
                      "Continuous learning and improvement"
                    ].map((value, i) => (
                      <li key={i} className="flex items-start">
                        <CheckCircle2 className="h-5 w-5 text-venture-500 mr-2 shrink-0 mt-0.5" />
                        <span>{value}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            </div>
          </div>
          
          <div className="lg:w-1/2 space-y-6 animate-fade-in" style={{ animationDelay: "0.3s" }}>
            <h2 className="mb-4">
              Why Choose <span className="gradient-text">Booming Venture</span>
            </h2>
            <p className="text-xl text-foreground/80">
              We combine strategic thinking with cutting-edge technology to help businesses 
              achieve exceptional growth in today's competitive marketplace.
            </p>
            
            <div className="grid grid-cols-2 gap-4 my-8">
              <div className="col-span-2 md:col-span-1">
                <img 
                  src="/lovable-uploads/c2f699f1-a432-4f51-956f-3a004df75a1c.png" 
                  alt="Diverse business professionals collaborating around digital displays in modern office"
                  className="w-full h-52 object-cover rounded-lg mb-4"
                />
              </div>
              <div className="col-span-2 md:col-span-1">
                <img 
                  src="/lovable-uploads/64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6.png" 
                  alt="Professional team working on digital analytics with interactive screens"
                  className="w-full h-52 object-cover rounded-lg mb-4"
                />
              </div>
            </div>
            
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
              <div className="bg-booming-50 p-6 rounded-lg">
                <h3 className="text-xl font-semibold text-booming-700 mb-2">Expertise</h3>
                <p className="text-foreground/70">
                  Our team brings decades of combined experience in business growth, marketing, 
                  and AI implementation from the heart of the Netherlands.
                </p>
              </div>
              
              <div className="bg-venture-50 p-6 rounded-lg">
                <h3 className="text-xl font-semibold text-venture-700 mb-2">Personalization</h3>
                <p className="text-foreground/70">
                  We create custom strategies tailored to your specific business goals and market position.
                </p>
              </div>
              
              <div className="bg-venture-50 p-6 rounded-lg">
                <h3 className="text-xl font-semibold text-venture-700 mb-2">AI Technology</h3>
                <p className="text-foreground/70">
                  Leverage advanced AI and data analytics to make informed decisions that drive growth.
                </p>
              </div>
              
              <div className="bg-booming-50 p-6 rounded-lg">
                <h3 className="text-xl font-semibold text-booming-700 mb-2">Proven Results</h3>
                <p className="text-foreground/70">
                  We focus on delivering measurable outcomes with clear KPIs and performance metrics.
                </p>
              </div>
            </div>
            
            <div className="pt-4">
              <Button 
                className="bg-booming-600 hover:bg-booming-700 text-white font-medium px-8 py-6 h-auto"
                onClick={() => window.location.href = "/about"}
              >
                Learn More About Us
              </Button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default AboutSection;
