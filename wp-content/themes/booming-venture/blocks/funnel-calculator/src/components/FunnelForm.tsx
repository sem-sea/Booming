
import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Calculator } from "lucide-react";
import type { FunnelData } from "@/pages/FunnelCalculator";

interface FunnelFormProps {
  onCalculate: (data: FunnelData) => void;
}

const FunnelForm = ({ onCalculate }: FunnelFormProps) => {
  const [formData, setFormData] = useState<FunnelData>({
    visitors: 10000,
    optInRate: 15,
    conversionRate: 5,
    aov: 100,
  });

  const [errors, setErrors] = useState<Record<string, string>>({});

  const validateForm = () => {
    const newErrors: Record<string, string> = {};

    if (!formData.visitors || formData.visitors <= 0) {
      newErrors.visitors = "Visitors must be a positive number";
    }
    
    if (!formData.optInRate || formData.optInRate <= 0 || formData.optInRate > 100) {
      newErrors.optInRate = "Opt-in rate must be between 0 and 100";
    }
    
    if (!formData.conversionRate || formData.conversionRate <= 0 || formData.conversionRate > 100) {
      newErrors.conversionRate = "Conversion rate must be between 0 and 100";
    }
    
    if (!formData.aov || formData.aov <= 0) {
      newErrors.aov = "Average order value must be a positive number";
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (validateForm()) {
      onCalculate(formData);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({
      ...prev,
      [name]: parseFloat(value) || 0,
    }));
  };

  return (
    <Card className="shadow-lg border-t-4 border-t-booming-500">
      <CardHeader>
        <CardTitle className="text-2xl">Funnel Metrics</CardTitle>
        <CardDescription>
          Enter your current funnel metrics to see where you're losing revenue
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-6">
          <div className="space-y-4">
            <div>
              <Label htmlFor="visitors" className="text-base">
                Monthly Website Visitors
              </Label>
              <Input
                id="visitors"
                name="visitors"
                type="number"
                placeholder="10,000"
                value={formData.visitors}
                onChange={handleChange}
                className={errors.visitors ? "border-destructive" : ""}
              />
              {errors.visitors && (
                <p className="text-sm text-destructive mt-1">{errors.visitors}</p>
              )}
            </div>

            <div>
              <Label htmlFor="optInRate" className="text-base">
                Opt-in Rate (%)
              </Label>
              <Input
                id="optInRate"
                name="optInRate"
                type="number"
                placeholder="15"
                value={formData.optInRate}
                onChange={handleChange}
                className={errors.optInRate ? "border-destructive" : ""}
              />
              {errors.optInRate && (
                <p className="text-sm text-destructive mt-1">{errors.optInRate}</p>
              )}
            </div>

            <div>
              <Label htmlFor="conversionRate" className="text-base">
                Conversion Rate (%)
              </Label>
              <Input
                id="conversionRate"
                name="conversionRate"
                type="number"
                placeholder="5"
                value={formData.conversionRate}
                onChange={handleChange}
                className={errors.conversionRate ? "border-destructive" : ""}
              />
              {errors.conversionRate && (
                <p className="text-sm text-destructive mt-1">{errors.conversionRate}</p>
              )}
            </div>

            <div>
              <Label htmlFor="aov" className="text-base">
                Average Order Value ($)
              </Label>
              <Input
                id="aov"
                name="aov"
                type="number"
                placeholder="100"
                value={formData.aov}
                onChange={handleChange}
                className={errors.aov ? "border-destructive" : ""}
              />
              {errors.aov && (
                <p className="text-sm text-destructive mt-1">{errors.aov}</p>
              )}
            </div>
          </div>

          <Button 
            type="submit" 
            size="lg" 
            className="w-full bg-gradient-to-r from-booming-600 to-venture-600 hover:from-booming-700 hover:to-venture-700"
          >
            <Calculator className="mr-2" />
            Calculate Results
          </Button>
        </form>
      </CardContent>
    </Card>
  );
};

export default FunnelForm;
