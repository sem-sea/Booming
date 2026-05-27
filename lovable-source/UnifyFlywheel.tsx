
import { useState } from "react";
import { Search, Mail, Settings, BarChart, Target } from "lucide-react";
import { Card } from "@/components/ui/card";
import { TooltipProvider, Tooltip, TooltipTrigger, TooltipContent } from "@/components/ui/tooltip";

const UnifyFlywheel = () => {
  const [hoveredPhase, setHoveredPhase] = useState<string | null>(null);

  const phases = [
    { id: "understand", label: "Understand", icon: Search, description: "Strategic clarity before execution" },
    { id: "nurture", label: "Nurture", icon: Mail, description: "Intent-driven progression through funnel" },
    { id: "integrate", label: "Integrate", icon: Settings, description: "Building the operational backbone" },
    { id: "forecast", label: "Forecast", icon: BarChart, description: "Predict ROI before you spend" },
    { id: "yield", label: "Yield", icon: Target, description: "Execute, learn, and optimize in loops" }
  ];

  return (
    <TooltipProvider>
      <div className="relative w-full max-w-2xl mx-auto">
        <div className="relative w-96 h-96 mx-auto">
          {/* Center Hub */}
          <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 bg-gradient-to-r from-booming-500 to-venture-500 rounded-full flex items-center justify-center text-white font-bold text-lg z-10">
            UNIFY
          </div>
          
          {/* Phase Segments */}
          {phases.map((phase, index) => {
            const angle = (index * 72) - 90; // 360/5 = 72 degrees per segment
            const radius = 120;
            const x = Math.cos(angle * Math.PI / 180) * radius;
            const y = Math.sin(angle * Math.PI / 180) * radius;
            
            return (
              <Tooltip key={phase.id}>
                <TooltipTrigger asChild>
                  <div
                    className={`absolute w-20 h-20 transform -translate-x-1/2 -translate-y-1/2 cursor-pointer transition-all duration-300 ${
                      hoveredPhase === phase.id ? 'scale-110' : 'scale-100'
                    }`}
                    style={{
                      left: `calc(50% + ${x}px)`,
                      top: `calc(50% + ${y}px)`
                    }}
                    onMouseEnter={() => setHoveredPhase(phase.id)}
                    onMouseLeave={() => setHoveredPhase(null)}
                  >
                    <Card className="w-full h-full bg-white border-2 border-booming-200 hover:border-booming-400 flex flex-col items-center justify-center p-2">
                      <phase.icon className="h-6 w-6 text-booming-600 mb-1" />
                      <span className="text-xs font-semibold text-center">{phase.label}</span>
                    </Card>
                  </div>
                </TooltipTrigger>
                <TooltipContent>
                  <p className="font-medium">{phase.label}</p>
                  <p className="text-sm text-gray-600">{phase.description}</p>
                </TooltipContent>
              </Tooltip>
            );
          })}
          
          {/* Outer Ring Tools */}
          <div className="absolute inset-0 border-2 border-dashed border-booming-200 rounded-full opacity-50"></div>
          {["CRM", "Outreach", "AI", "Models", "Analytics"].map((tool, index) => {
            const angle = (index * 72) - 90;
            const radius = 160;
            const x = Math.cos(angle * Math.PI / 180) * radius;
            const y = Math.sin(angle * Math.PI / 180) * radius;
            
            return (
              <div
                key={tool}
                className="absolute text-xs bg-gray-100 px-2 py-1 rounded-full transform -translate-x-1/2 -translate-y-1/2"
                style={{
                  left: `calc(50% + ${x}px)`,
                  top: `calc(50% + ${y}px)`
                }}
              >
                {tool}
              </div>
            );
          })}
        </div>
      </div>
    </TooltipProvider>
  );
};

export default UnifyFlywheel;
