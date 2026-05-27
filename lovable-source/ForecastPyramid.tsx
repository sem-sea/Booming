
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { TrendingUp, Calculator, DollarSign } from "lucide-react";

const ForecastPyramid = () => {
  const [activeLayer, setActiveLayer] = useState<string | null>(null);

  const layers = [
    {
      id: "scenario",
      name: "Scenario Planning",
      icon: DollarSign,
      description: "Best/worst/likely case modeling",
      details: "Multiple ROI scenarios based on different conversion rates and market conditions"
    },
    {
      id: "predictive", 
      name: "Predictive Modeling",
      icon: Calculator,
      description: "AI-powered forecasting",
      details: "Machine learning algorithms predict future performance based on historical data"
    },
    {
      id: "baseline",
      name: "Baseline Metrics",
      icon: TrendingUp,
      description: "Current performance data",
      details: "CAC, LTV, conversion rates, and other key performance indicators"
    }
  ];

  return (
    <div className="flex flex-col items-center space-y-4">
      {layers.map((layer, index) => (
        <Card
          key={layer.id}
          className={`p-6 cursor-pointer transition-all duration-300 ${
            activeLayer === layer.id
              ? 'shadow-lg border-booming-300 bg-gradient-to-r from-booming-50 to-venture-50'
              : 'hover:shadow-md'
          }`}
          style={{ width: `${24 - index * 4}rem` }}
        >
          <Button
            variant="ghost"
            className="w-full h-auto p-0 text-left"
            onClick={() => setActiveLayer(activeLayer === layer.id ? null : layer.id)}
          >
            <div className="flex items-center gap-4 w-full">
              <layer.icon className="h-8 w-8 text-booming-600" />
              <div className="flex-1">
                <h3 className="font-bold text-lg">{layer.name}</h3>
                <p className="text-sm text-gray-600">{layer.description}</p>
              </div>
            </div>
          </Button>
          
          {activeLayer === layer.id && (
            <div className="mt-4 pt-4 border-t animate-fade-in">
              <p className="text-sm">{layer.details}</p>
            </div>
          )}
        </Card>
      ))}
    </div>
  );
};

export default ForecastPyramid;
