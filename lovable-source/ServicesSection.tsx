
import { 
  BarChart3, 
  Rocket, 
  BrainCircuit, 
  TrendingUp,
  CheckCircle
} from "lucide-react";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { AspectRatio } from "@/components/ui/aspect-ratio";

const ServiceCard = ({ 
  icon: Icon, 
  title, 
  description, 
  benefits, 
  delay,
  image,
  alt
}: { 
  icon: React.ElementType; 
  title: string; 
  description: string; 
  benefits: string[];
  delay: string;
  image: string;
  alt: string;
}) => {
  return (
    <Card className="border border-border animate-fade-in h-full" style={{ animationDelay: delay }}>
      <CardHeader className="pb-2">
        <div className="h-12 w-12 rounded-lg bg-gradient-to-br from-booming-100 to-venture-100 flex items-center justify-center mb-2">
          <Icon className="h-6 w-6 text-booming-600" />
        </div>
        <CardTitle className="text-xl">{title}</CardTitle>
      </CardHeader>
      <CardContent>
        <CardDescription className="text-foreground/70 mb-4">{description}</CardDescription>
        <div className="mb-4 rounded-md overflow-hidden">
          <AspectRatio ratio={16/9}>
            <img 
              src={image} 
              alt={alt} 
              className="object-cover w-full h-full" 
            />
          </AspectRatio>
        </div>
        <ul className="space-y-2">
          {benefits.map((benefit, index) => (
            <li key={index} className="flex items-start">
              <CheckCircle className="h-5 w-5 text-venture-500 mr-2 shrink-0 mt-0.5" />
              <span className="text-sm text-foreground/80">{benefit}</span>
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  );
};

const ServicesSection = () => {
  const services = [
    {
      icon: BarChart3,
      title: "Strategic Consulting",
      description: "Tailored growth strategies to help your business reach its full potential.",
      image: "/lovable-uploads/c2f699f1-a432-4f51-956f-3a004df75a1c.png",
      alt: "Business team collaborating on strategic planning with data visualization screens",
      benefits: [
        "Customized business growth plans",
        "Market analysis and competitor research",
        "Revenue optimization strategies",
        "Business model innovation"
      ],
      delay: "0.1s"
    },
    {
      icon: Rocket,
      title: "Performance Marketing",
      description: "Data-driven marketing campaigns that deliver measurable results.",
      image: "/lovable-uploads/64a1eea5-ff4d-4a89-83d2-1e2e9c5258d6.png",
      alt: "Diverse marketing team collaborating in a modern workspace with digital displays",
      benefits: [
        "Multi-channel marketing strategies",
        "Conversion rate optimization",
        "Customer acquisition campaigns",
        "ROI-focused advertising"
      ],
      delay: "0.2s"
    },
    {
      icon: BrainCircuit,
      title: "AI-Powered Solutions",
      description: "Leverage cutting-edge AI technology to optimize your business operations.",
      image: "/lovable-uploads/ccf94e11-7a43-4518-b095-62b335e3c4d7.png",
      alt: "Tech professional working with AI visualization interfaces in a futuristic setting",
      benefits: [
        "Predictive analytics implementation",
        "Automated marketing workflows",
        "Customer behavior analysis",
        "AI-driven decision support"
      ],
      delay: "0.3s"
    },
    {
      icon: TrendingUp,
      title: "Growth Optimization",
      description: "Comprehensive programs to scale your business efficiently and sustainably.",
      image: "/lovable-uploads/12b0da47-c27a-4cb5-b98a-61b5ddd8dcf4.png",
      alt: "Diverse team celebrating success while analyzing growth charts on laptops",
      benefits: [
        "Scalable growth frameworks",
        "Process optimization",
        "Cross-functional team alignment",
        "Long-term growth planning"
      ],
      delay: "0.4s"
    }
  ];

  return (
    <section id="services" className="py-16 md:py-24 bg-gradient-to-b from-white to-booming-50/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="text-center max-w-3xl mx-auto mb-16 animate-fade-in">
          <h2 className="mb-4">Our Services</h2>
          <p className="text-xl text-foreground/70">
            Comprehensive solutions designed to help your business thrive in today's competitive landscape.
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
          {services.map((service, index) => (
            <ServiceCard
              key={index}
              icon={service.icon}
              title={service.title}
              description={service.description}
              benefits={service.benefits}
              delay={service.delay}
              image={service.image}
              alt={service.alt}
            />
          ))}
        </div>
        
        <div className="text-center animate-fade-in" style={{ animationDelay: "0.5s" }}>
          <Button 
            className="bg-booming-600 hover:bg-booming-700 text-white font-medium px-8 py-6 h-auto text-lg"
            onClick={() => window.location.href = "/services"}
          >
            Explore All Services
          </Button>
        </div>
      </div>
    </section>
  );
};

export default ServicesSection;
