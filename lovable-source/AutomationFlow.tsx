
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { ArrowRight, Download, Mail, MessageSquare, Database, BarChart } from "lucide-react";

const AutomationFlow = () => {
  const [hoveredStep, setHoveredStep] = useState<string | null>(null);

  const steps = [
    {
      id: "download",
      name: "Lead Magnet Download",
      icon: Download,
      trigger: "User submits email",
      action: "Add to CRM, start nurture sequence"
    },
    {
      id: "email",
      name: "Email Opened",
      icon: Mail,
      trigger: "Email engagement detected",
      action: "Score lead, trigger next sequence"
    },
    {
      id: "slack",
      name: "Slack Alert",
      icon: MessageSquare,
      trigger: "High-value action detected",
      action: "Notify sales team immediately"
    },
    {
      id: "crm",
      name: "CRM Update",
      icon: Database,
      trigger: "New lead score threshold",
      action: "Update lead status, assign owner"
    },
    {
      id: "retarget",
      name: "Ad Retargeting",
      icon: BarChart,
      trigger: "No action after 3 days",
      action: "Launch targeted ad campaign"
    }
  ];

  return (
    <div className="flex flex-wrap justify-center items-center gap-4">
      {steps.map((step, index) => (
        <div key={step.id} className="flex items-center">
          <Card
            className={`p-4 w-48 cursor-pointer transition-all duration-300 ${
              hoveredStep === step.id
                ? 'shadow-lg border-booming-300 bg-gradient-to-br from-booming-50 to-venture-50'
                : 'hover:shadow-md'
            }`}
            onMouseEnter={() => setHoveredStep(step.id)}
            onMouseLeave={() => setHoveredStep(null)}
          >
            <div className="flex items-center gap-3 mb-2">
              <step.icon className="h-6 w-6 text-booming-600" />
              <h4 className="font-semibold text-sm">{step.name}</h4>
            </div>
            
            {hoveredStep === step.id && (
              <div className="text-xs space-y-1 animate-fade-in">
                <p><strong>Trigger:</strong> {step.trigger}</p>
                <p><strong>Action:</strong> {step.action}</p>
              </div>
            )}
          </Card>
          
          {index < steps.length - 1 && (
            <ArrowRight className="h-5 w-5 text-booming-400 mx-2" />
          )}
        </div>
      ))}
    </div>
  );
};

export default AutomationFlow;
