
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Users, MessageSquare, Target } from "lucide-react";

const MarketClarityMap = () => {
  const [hoveredPersona, setHoveredPersona] = useState<string | null>(null);

  const personas = [
    {
      id: "startup",
      name: "Startup Founder",
      behavior: "DIY approach, budget-conscious, fast decisions",
      objections: "Cost, time investment, complexity",
      cta: "Free tools, quick wins, ROI proof"
    },
    {
      id: "smb",
      name: "SMB Owner",
      behavior: "Delegation-focused, proven solutions, relationship-driven",
      objections: "Trust, implementation time, results guarantee",
      cta: "Case studies, done-for-you services, partnerships"
    },
    {
      id: "enterprise",
      name: "Enterprise Manager",
      behavior: "Process-oriented, approval chains, scalability focus",
      objections: "Integration complexity, team training, compliance",
      cta: "White papers, demos, pilot programs"
    }
  ];

  return (
    <div className="grid md:grid-cols-3 gap-6">
      {personas.map((persona) => (
        <Card
          key={persona.id}
          className={`p-6 cursor-pointer transition-all duration-300 ${
            hoveredPersona === persona.id
              ? 'shadow-lg border-booming-300 bg-gradient-to-br from-booming-50 to-venture-50'
              : 'hover:shadow-md'
          }`}
          onMouseEnter={() => setHoveredPersona(persona.id)}
          onMouseLeave={() => setHoveredPersona(null)}
        >
          <div className="flex items-center gap-3 mb-4">
            <Users className="h-8 w-8 text-booming-600" />
            <h3 className="font-bold text-lg">{persona.name}</h3>
          </div>
          
          {hoveredPersona === persona.id && (
            <div className="space-y-3 animate-fade-in">
              <div className="flex items-start gap-2">
                <Target className="h-4 w-4 text-venture-500 mt-1 flex-shrink-0" />
                <div>
                  <p className="text-sm font-medium">Behavior:</p>
                  <p className="text-sm text-gray-600">{persona.behavior}</p>
                </div>
              </div>
              
              <div className="flex items-start gap-2">
                <MessageSquare className="h-4 w-4 text-red-500 mt-1 flex-shrink-0" />
                <div>
                  <p className="text-sm font-medium">Objections:</p>
                  <p className="text-sm text-gray-600">{persona.objections}</p>
                </div>
              </div>
              
              <div className="flex items-start gap-2">
                <Target className="h-4 w-4 text-green-500 mt-1 flex-shrink-0" />
                <div>
                  <p className="text-sm font-medium">CTA Response:</p>
                  <p className="text-sm text-gray-600">{persona.cta}</p>
                </div>
              </div>
            </div>
          )}
        </Card>
      ))}
    </div>
  );
};

export default MarketClarityMap;
