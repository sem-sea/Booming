
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Search, Settings, FileText, Rocket } from "lucide-react";

const ModuleCards = () => {
  const [hoveredModule, setHoveredModule] = useState<string | null>(null);

  const modules = [
    {
      id: "strategy",
      title: "Strategy",
      subtitle: "Market clarity & positioning",
      icon: Search,
      preview: "Persona mapping, competitor analysis, messaging framework"
    },
    {
      id: "automation", 
      title: "Automation",
      subtitle: "Tool integration & workflows",
      icon: Settings,
      preview: "CRM setup, email sequences, Slack integrations, AI triggers"
    },
    {
      id: "prompts",
      title: "Prompts",
      subtitle: "AI-powered content creation",
      icon: FileText,
      preview: "Email templates, ad copy, social posts, nurture sequences"
    },
    {
      id: "execution",
      title: "Execution Plan", 
      subtitle: "30-day implementation roadmap",
      icon: Rocket,
      preview: "Week-by-week tasks, growth loops, optimization cycles"
    }
  ];

  return (
    <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      {modules.map((module) => (
        <Card
          key={module.id}
          className={`p-6 cursor-pointer transition-all duration-300 ${
            hoveredModule === module.id
              ? 'shadow-lg border-booming-300 bg-gradient-to-br from-booming-50 to-venture-50 transform -translate-y-1'
              : 'hover:shadow-md'
          }`}
          onMouseEnter={() => setHoveredModule(module.id)}
          onMouseLeave={() => setHoveredModule(null)}
        >
          <div className="flex flex-col items-center text-center space-y-4">
            <div className="w-16 h-16 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center">
              <module.icon className="h-8 w-8 text-white" />
            </div>
            
            <div>
              <h3 className="font-bold text-lg mb-1">{module.title}</h3>
              <p className="text-sm text-gray-600 mb-3">{module.subtitle}</p>
            </div>
            
            {hoveredModule === module.id && (
              <div className="animate-fade-in">
                <p className="text-sm text-gray-700 italic">
                  What's inside: {module.preview}
                </p>
              </div>
            )}
          </div>
        </Card>
      ))}
    </div>
  );
};

export default ModuleCards;
