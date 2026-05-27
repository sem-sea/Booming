
import {
  Carousel,
  CarouselContent,
  CarouselItem,
  CarouselNext,
  CarouselPrevious,
} from "@/components/ui/carousel";
import { Card, CardContent } from "@/components/ui/card";
import { AspectRatio } from "@/components/ui/aspect-ratio";
import { Quote } from "lucide-react";

const TestimonialsSection = () => {
  const testimonials = [
    {
      name: "Sarah Johnson",
      position: "CEO",
      content:
        "Booming Venture transformed our marketing strategy completely. Their AI-driven approach increased our conversion rates by 42% in just three months.",
      image: "/lovable-uploads/4e357139-5a7e-4336-8796-94013f33dc3d.png",
      alt: "Professional business woman in a meeting discussing strategy with a colleague"
    },
    {
      name: "Michael Chen",
      position: "Marketing Director",
      content:
        "The strategic consulting we received helped us identify untapped market opportunities. Their team is incredibly knowledgeable and responsive.",
      image: "/lovable-uploads/c2f699f1-a432-4f51-956f-3a004df75a1c.png",
      alt: "Two professional businessmen having a productive meeting and discussion"
    },
    {
      name: "Emily Rodriguez",
      position: "Founder",
      content:
        "Working with Booming Venture gave us the competitive edge we needed. Their performance marketing campaigns delivered exceptional ROI.",
      image: "/lovable-uploads/ccf94e11-7a43-4518-b095-62b335e3c4d7.png",
      alt: "Professional woman presenting to a team in a modern office environment"
    }
  ];

  return (
    <section className="py-16 md:py-24 bg-booming-50/30">
      <div className="container mx-auto px-4 md:px-6">
        <div className="text-center max-w-3xl mx-auto mb-16 animate-fade-in">
          <h2 className="mb-4">Client Success Stories</h2>
          <p className="text-xl text-foreground/70">
            Hear from businesses we've helped scale through innovative strategies
          </p>
        </div>

        <Carousel className="w-full max-w-5xl mx-auto">
          <CarouselContent>
            {testimonials.map((testimonial, index) => (
              <CarouselItem key={index} className="md:basis-1/1 lg:basis-1/1 pl-4">
                <Card className="border shadow-md overflow-hidden">
                  <CardContent className="p-0">
                    <div className="flex flex-col md:flex-row">
                      <div className="md:w-1/3 relative">
                        <AspectRatio ratio={3/4} className="h-full">
                          <img 
                            src={testimonial.image} 
                            alt={testimonial.alt}
                            className="object-cover w-full h-full" 
                          />
                        </AspectRatio>
                      </div>
                      <div className="md:w-2/3 p-6 md:p-8 flex flex-col justify-center">
                        <Quote className="h-10 w-10 text-booming-300 mb-4 opacity-50" />
                        <p className="text-lg mb-6 italic text-foreground/80">
                          "{testimonial.content}"
                        </p>
                        <div>
                          <h4 className="font-bold text-lg">{testimonial.name}</h4>
                          <p className="text-foreground/70">{testimonial.position}</p>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </CarouselItem>
            ))}
          </CarouselContent>
          <div className="flex justify-center mt-8 gap-2">
            <CarouselPrevious className="relative static left-0 right-auto translate-y-0" />
            <CarouselNext className="relative static right-0 left-auto translate-y-0" />
          </div>
        </Carousel>
      </div>
    </section>
  );
};

export default TestimonialsSection;
