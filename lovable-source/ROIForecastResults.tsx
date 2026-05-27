
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { PhoneCall } from "lucide-react";
import { useToast } from "@/hooks/use-toast";
import type { ROIForecastData } from "@/pages/ROIForecaster";
import { useRef } from "react";
import ROIFunnelChart from "./ROIFunnelChart";
import ROIMetricsPanel from "./ROIMetricsPanel";
import { addContactToBrevo } from "@/services/brevoService";

interface ROIForecastResultsProps {
  data: ROIForecastData;
  onTriggerWebhook: (data: ROIForecastData) => Promise<boolean>;
  webhookTriggered: boolean;
}

const ROIForecastResults = ({ data, onTriggerWebhook, webhookTriggered }: ROIForecastResultsProps) => {
  const { toast } = useToast();

  const handleBookCall = async () => {
    // Track ROI forecaster usage in Brevo
    try {
      console.log("ROI forecast completed - user clicked Book a Call");
      
      // Navigate to homepage contact section
      window.location.href = "/#contact";
    } catch (error) {
      console.error("Error tracking ROI forecast completion:", error);
      // Still redirect even if tracking fails
      window.location.href = "/#contact";
    }
  };

  return (
    <div>
      <Card className="shadow-lg border-t-4 border-t-booming-500">
        <CardHeader>
          <CardTitle className="text-2xl">Your ROI Forecast</CardTitle>
        </CardHeader>
        <CardContent className="space-y-8">
          <ROIFunnelChart data={data} />
          <ROIMetricsPanel data={data} />
        </CardContent>
        <CardFooter className="flex flex-col space-y-4">
          <p className="text-muted-foreground text-sm italic">
            Based on industry benchmarks and AI-powered optimization potential
          </p>
          <Button 
            className="w-full bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700"
            size="lg"
            onClick={handleBookCall}
          >
            <PhoneCall className="mr-2" />
            Book a Call
          </Button>
        </CardFooter>
      </Card>
    </div>
  );
};

export default ROIForecastResults;
