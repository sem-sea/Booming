
import { useState } from "react";
import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Mail, BarChart, TestTube, MessageCircle, RefreshCw } from "lucide-react";

const LaunchTracker = () => {
  const [activeWeek, setActiveWeek] = useState(1);

  const weeks = [
    {
      week: 1,
      tasks: [
        { icon: Mail, task: "Launch nurture sequence", status: "completed" },
        { icon: BarChart, task: "Set up analytics tracking", status: "completed" },
        { icon: TestTube, task: "A/B test subject lines", status: "in-progress" }
      ]
    },
    {
      week: 2, 
      tasks: [
        { icon: BarChart, task: "Launch ad campaigns", status: "pending" },
        { icon: MessageCircle, task: "Gather feedback", status: "pending" },
        { icon: TestTube, task: "Test landing pages", status: "pending" }
      ]
    },
    {
      week: 3,
      tasks: [
        { icon: RefreshCw, task: "Optimize based on data", status: "pending" },
        { icon: Mail, task: "Refine nurture flows", status: "pending" },
        { icon: BarChart, task: "Scale winning campaigns", status: "pending" }
      ]
    },
    {
      week: 4,
      tasks: [
        { icon: BarChart, task: "Performance review", status: "pending" },
        { icon: RefreshCw, task: "Plan next 30 days", status: "pending" },
        { icon: MessageCircle, task: "Stakeholder updates", status: "pending" }
      ]
    }
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case "completed": return "bg-green-100 text-green-800";
      case "in-progress": return "bg-yellow-100 text-yellow-800";
      default: return "bg-gray-100 text-gray-800";
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex gap-2 justify-center">
        {[1, 2, 3, 4].map((week) => (
          <Button
            key={week}
            variant={activeWeek === week ? "default" : "outline"}
            onClick={() => setActiveWeek(week)}
            className="min-w-[80px]"
          >
            Week {week}
          </Button>
        ))}
      </div>
      
      <Card className="p-6">
        <h3 className="font-bold text-lg mb-4">Week {activeWeek} Tasks</h3>
        <div className="space-y-3">
          {weeks[activeWeek - 1].tasks.map((task, index) => (
            <div key={index} className="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50">
              <task.icon className="h-5 w-5 text-booming-600" />
              <span className="flex-1">{task.task}</span>
              <span className={`px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(task.status)}`}>
                {task.status.replace("-", " ")}
              </span>
            </div>
          ))}
        </div>
      </Card>
      
      <div className="flex justify-center">
        <Card className="p-4 bg-gradient-to-r from-booming-50 to-venture-50">
          <div className="flex items-center gap-3">
            <RefreshCw className="h-5 w-5 text-booming-600" />
            <span className="text-sm font-medium">Growth loops feed back into Week 1</span>
          </div>
        </Card>
      </div>
    </div>
  );
};

export default LaunchTracker;
