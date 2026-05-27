
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FileText, Calculator, Users, ArrowDown } from "lucide-react";

const NurtureTree = () => {
  const [activeStage, setActiveStage] = useState<string | null>(null);

  const stages = [
    {
      id: "tofu",
      name: "TOFU",
      title: "Top of Funnel",
      content: "Educational content, problem awareness",
      examples: ["Blog posts", "Guides", "Webinars"],
      trigger: "Downloaded lead magnet",
      icon: FileText
    },
    {
      id: "mofu",
      name: "MOFU", 
      title: "Middle of Funnel",
      content: "Solution comparison, trust building",
      examples: ["Case studies", "ROI calculators", "Demos"],
      trigger: "Engaged with TOFU content",
      icon: Calculator
    },
    {
      id: "bofu",
      name: "BOFU",
      title: "Bottom of Funnel", 
      content: "Decision support, objection handling",
      examples: ["Proposals", "Consultations", "Free trials"],
      trigger: "Requested pricing/demo",
      icon: Users
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-col items-center space-y-4">
        {stages.map((stage, index) => (
          <div key={stage.id} className="flex flex-col items-center">
            <Card 
              className={`p-6 w-80 cursor-pointer transition-all duration-300 ${
                activeStage === stage.id 
                  ? 'shadow-lg border-booming-300 bg-gradient-to-r from-booming-50 to-venture-50' 
                  : 'hover:shadow-md'
              }`}
            >
              <Button
                variant="ghost"
                className="w-full h-auto p-0 text-left"
                onClick={() => setActiveStage(activeStage === stage.id ? null : stage.id)}
              >
                <div className="flex items-center gap-4 w-full">
                  <stage.icon className="h-8 w-8 text-booming-600" />
                  <div className="flex-1">
                    <h3 className="font-bold text-lg">{stage.name}</h3>
                    <p className="text-sm text-gray-600">{stage.title}</p>
                  </div>
                </div>
              </Button>
              
              {activeStage === stage.id && (
                <div className="mt-4 pt-4 border-t animate-fade-in">
                  <p className="text-sm mb-3"><strong>Content:</strong> {stage.content}</p>
                  <p className="text-sm mb-3"><strong>Examples:</strong> {stage.examples.join(", ")}</p>
                  <p className="text-sm"><strong>Trigger:</strong> {stage.trigger}</p>
                </div>
              )}
            </Card>
            
            {index < stages.length - 1 && (
              <ArrowDown className="h-6 w-6 text-booming-400 my-2" />
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default NurtureTree;
