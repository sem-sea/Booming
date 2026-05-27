import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Progress } from "@/components/ui/progress";
import { Badge } from "@/components/ui/badge";
import { 
  AlertTriangle, 
  CheckCircle, 
  TrendingDown, 
  Target, 
  Clock, 
  Phone, 
  Mail,
  BarChart3,
  Shield,
  Zap
} from "lucide-react";
import { motion, AnimatePresence } from "framer-motion";

interface AssessmentQuestion {
  id: string;
  question: string;
  options: { value: number; label: string; }[];
}

interface BoardroomAssessmentProps {
  language: 'nl' | 'en';
}

const BoardroomAssessment = ({ language }: BoardroomAssessmentProps) => {
  const [currentStep, setCurrentStep] = useState(0);
  const [answers, setAnswers] = useState<Record<string, number>>({});
  const [leadInfo, setLeadInfo] = useState({
    firstName: '',
    lastName: '',
    email: '',
    company: '',
    phone: ''
  });
  const [showResults, setShowResults] = useState(false);
  const [score, setScore] = useState(0);
  const [riskLevel, setRiskLevel] = useState('');
  const [timeLeft, setTimeLeft] = useState(14 * 60); // 14 minutes for urgency

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft(prev => prev > 0 ? prev - 1 : 0);
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const formatTime = (seconds: number) => {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const content = {
    nl: {
      title: "Boardroom AI Readiness Quickscan",
      subtitle: "Ontdek in 3 minuten waar jouw marketing kwetsbaar is",
      urgencyText: "⚡ Deze scan sluit automatisch over",
      questions: [
        {
          id: 'attribution',
          question: 'Kun je exact aantonen welke marketing euro bijdraagt aan omzet?',
          options: [
            { value: 1, label: 'Ja, tot op de euro nauwkeurig' },
            { value: 2, label: 'Grotendeels, met enkele gaten' },
            { value: 3, label: 'Gedeeltelijk, maar niet volledig' },
            { value: 4, label: 'Nee, dit is een blind spot' }
          ]
        },
        {
          id: 'board_confidence',
          question: 'Hoe reageert jouw board op marketing budget aanvragen?',
          options: [
            { value: 1, label: 'Volledige steun en vertrouwen' },
            { value: 2, label: 'Meestal positief met enkele vragen' },
            { value: 3, label: 'Sceptisch, veel verantwoording nodig' },
            { value: 4, label: 'Weerstand en budget druk' }
          ]
        },
        {
          id: 'ai_readiness',
          question: 'Wat weet je over AI-marketing van je directe concurrenten?',
          options: [
            { value: 1, label: 'We monitoren en zijn voorop' },
            { value: 2, label: 'We weten grotendeels wat er speelt' },
            { value: 3, label: 'Beperkt inzicht in hun strategie' },
            { value: 4, label: 'Geen idee wat ze doen' }
          ]
        },
        {
          id: 'leak_detection',
          question: 'Hoe snel kun je marketing "lekken" identificeren en stoppen?',
          options: [
            { value: 1, label: 'Real-time dashboards en alerts' },
            { value: 2, label: 'Binnen een week detecteren' },
            { value: 3, label: 'Pas na maandrapportage' },
            { value: 4, label: 'Vaak te laat of helemaal niet' }
          ]
        },
        {
          id: 'predictability',
          question: 'Hoe voorspelbaar is jouw marketing ROI per kanaal?',
          options: [
            { value: 1, label: 'Zeer voorspelbaar met nauwkeurige forecasts' },
            { value: 2, label: 'Redelijk voorspelbaar' },
            { value: 3, label: 'Wisselvallige resultaten' },
            { value: 4, label: 'Onvoorspelbaar en volatiel' }
          ]
        }
      ],
      results: {
        excellent: {
          title: "🟢 Excellente Boardroom Positie",
          subtitle: "Score: 5-8 punten - Je bent goed voorbereid",
          description: "Je hebt sterke systemen, maar er zijn altijd verbeteringen mogelijk. Een gratis review kan verborgen kansen blootleggen.",
          action: "Claim je gratis competitive intelligence rapport"
        },
        moderate: {
          title: "🟡 Gemiddelde Boardroom Risico's", 
          subtitle: "Score: 9-14 punten - Kwetsbare gebieden gedetecteerd",
          description: "Je loopt risico op budget cuts of verlies van vertrouwen. Directe actie voorkomt escalatie naar de board.",
          action: "Plan direct een Boardroom Clarity Sprint"
        },
        critical: {
          title: "🔴 Kritieke Boardroom Dreiging",
          subtitle: "Score: 15-20 punten - Acute interventie nodig", 
          description: "Je positie is ernstig bedreigd. CMO's in jouw situatie verliezen vaak hun rol binnen 6 maanden zonder directe actie.",
          action: "Urgent: Plan een crisis-interventie gesprek"
        }
      },
      form: {
        title: "Ontvang Je Persoonlijke Boardroom Rapport",
        firstName: "Voornaam",
        lastName: "Achternaam", 
        email: "E-mail adres",
        company: "Bedrijf",
        phone: "Telefoonnummer",
        submit: "Stuur Mijn Rapport + Plan Gesprek",
        privacy: "We respecteren je privacy. Geen spam, alleen waardevolle inzichten."
      },
      cta: {
        phone: "Bel Direct: +316 130 132 66",
        email: "Email: info@boomingventure.com",
        urgency: "⚠️ Wacht niet tot je volgende board meeting je laatste wordt"
      }
    },
    en: {
      title: "Boardroom Readiness Quickscan", 
      subtitle: "Discover in 3 minutes where your marketing is vulnerable",
      urgencyText: "⚡ This scan closes automatically in",
      questions: [
        {
          id: 'attribution',
          question: 'Can you prove exactly which marketing euro contributes to your revenue?',
          options: [
            { value: 1, label: 'Yes, down to the euro' },
            { value: 2, label: 'Mostly, with some gaps' },
            { value: 3, label: 'Partially, but not completely' },
            { value: 4, label: 'No, this is a blind spot' }
          ]
        },
        {
          id: 'board_confidence', 
          question: 'How does your board react to marketing budget requests?',
          options: [
            { value: 1, label: 'Full support and confidence' },
            { value: 2, label: 'Usually positive with some questions' },
            { value: 3, label: 'Skeptical, lots of justification needed' },
            { value: 4, label: 'Resistance and budget pressure' }
          ]
        },
        {
          id: 'ai_readiness',
          question: 'What do you know about your direct competitors\' AI marketing?',
          options: [
            { value: 1, label: 'We monitor and are ahead' },
            { value: 2, label: 'We know most of what\'s happening' },
            { value: 3, label: 'Limited insight into their strategy' },
            { value: 4, label: 'No idea what they\'re doing' }
          ]
        },
        {
          id: 'leak_detection',
          question: 'How quickly can you identify and stop marketing "leaks"?',
          options: [
            { value: 1, label: 'Real-time dashboards and alerts' },
            { value: 2, label: 'Detect within a week' },
            { value: 3, label: 'Only after monthly reporting' },
            { value: 4, label: 'Often too late or not at all' }
          ]
        },
        {
          id: 'predictability',
          question: 'How predictable is your marketing ROI per channel?',
          options: [
            { value: 1, label: 'Very predictable with accurate forecasts' },
            { value: 2, label: 'Reasonably predictable' },
            { value: 3, label: 'Variable results' },
            { value: 4, label: 'Unpredictable and volatile' }
          ]
        }
      ],
      results: {
        excellent: {
          title: "🟢 Excellent Boardroom Position",
          subtitle: "Score: 5-8 points - You're well prepared", 
          description: "You have strong systems, but there are always improvements possible. A free review can reveal hidden opportunities.",
          action: "Claim your free competitive intelligence report"
        },
        moderate: {
          title: "🟡 Moderate Boardroom Risks",
          subtitle: "Score: 9-14 points - Vulnerable areas detected",
          description: "You risk budget cuts or loss of confidence. Direct action prevents escalation to the board.",
          action: "Schedule a Boardroom Clarity Sprint immediately"
        },
        critical: {
          title: "🔴 Critical Boardroom Threat", 
          subtitle: "Score: 15-20 points - Acute intervention needed",
          description: "Your position is seriously threatened. CMOs in your situation often lose their role within 6 months without direct action.",
          action: "Urgent: Schedule a crisis intervention call"
        }
      },
      form: {
        title: "Receive Your Personal Boardroom Report",
        firstName: "First Name",
        lastName: "Last Name",
        email: "Email Address", 
        company: "Company",
        phone: "Phone Number",
        submit: "Send My Report + Schedule Call",
        privacy: "We respect your privacy. No spam, only valuable insights."
      },
      cta: {
        phone: "Call Direct: +316 130 132 66",
        email: "Email: info@boomingventure.com", 
        urgency: "⚠️ Don't wait until your next board meeting becomes your last"
      }
    }
  };

  const currentContent = content[language];
  const questions = currentContent.questions;
  const totalSteps = questions.length; // Remove lead form step
  const progress = (currentStep / totalSteps) * 100;

  const handleAnswer = (questionId: string, value: number) => {
    setAnswers(prev => ({ ...prev, [questionId]: value }));
    setTimeout(() => {
      if (currentStep === questions.length - 1) {
        // Last question, calculate results immediately
        const newAnswers = { ...answers, [questionId]: value };
        const totalScore = Object.values(newAnswers).reduce((sum, val) => sum + val, 0);
        setScore(totalScore);
        
        if (totalScore <= 8) {
          setRiskLevel('excellent');
        } else if (totalScore <= 14) {
          setRiskLevel('moderate'); 
        } else {
          setRiskLevel('critical');
        }
        
        setShowResults(true);
      } else {
        setCurrentStep(prev => prev + 1);
      }
    }, 300);
  };

  const calculateResults = () => {
    const totalScore = Object.values(answers).reduce((sum, value) => sum + value, 0);
    setScore(totalScore);
    
    if (totalScore <= 8) {
      setRiskLevel('excellent');
    } else if (totalScore <= 14) {
      setRiskLevel('moderate'); 
    } else {
      setRiskLevel('critical');
    }
    
    setShowResults(true);
  };

  const handleSubmit = () => {
    calculateResults();
    // Just show results, don't open email automatically
  };

  const handleDirectContact = (type: 'phone' | 'email') => {
    if (type === 'phone') {
      window.open('tel:+31613013266', '_self');
    } else {
      window.open('mailto:info@boomingventure.com?subject=Urgent Boardroom Quickscan - Need Help', '_blank');
    }
  };

  if (showResults) {
    const result = currentContent.results[riskLevel as keyof typeof currentContent.results];
    
    return (
      <motion.div 
        initial={{ opacity: 0, scale: 0.9 }}
        animate={{ opacity: 1, scale: 1 }}
        className="max-w-4xl mx-auto"
      >
        <Card className="border-2 border-primary/30 shadow-2xl bg-gradient-to-br from-background to-muted/20">
          <CardHeader className="text-center pb-6">
            <div className="mb-6">
              <Badge variant="default" className="text-lg px-6 py-3 bg-gradient-to-r from-primary to-venture-600">
                <Target className="h-5 w-5 mr-2" />
                {language === 'nl' ? 'BOARDROOM ANALYSE COMPLEET' : 'BOARDROOM ANALYSIS COMPLETE'}
              </Badge>
            </div>
            <CardTitle className="text-3xl md:text-4xl mb-4 bg-gradient-to-r from-primary to-venture-600 bg-clip-text text-transparent">
              {result.title}
            </CardTitle>
            <p className="text-xl font-semibold text-muted-foreground">{result.subtitle}</p>
          </CardHeader>
          
          <CardContent className="space-y-8">
            {/* Risk Level Visualization */}
            <div className="bg-gradient-to-r from-muted/50 to-muted/30 border border-primary/20 rounded-2xl p-6">
              <div className="grid md:grid-cols-3 gap-6 mb-6">
                <div className="text-center">
                  <div className={`w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-3 ${
                    riskLevel === 'excellent' ? 'bg-green-500/20 text-green-500' :
                    riskLevel === 'moderate' ? 'bg-yellow-500/20 text-yellow-500' :
                    'bg-destructive/20 text-destructive'
                  }`}>
                    <span className="text-2xl font-bold">{score}</span>
                  </div>
                  <p className="text-sm font-medium">
                    {language === 'nl' ? 'Risico Score' : 'Risk Score'}
                  </p>
                </div>
                
                <div className="text-center">
                  <div className="w-20 h-20 mx-auto rounded-full bg-primary/20 text-primary flex items-center justify-center mb-3">
                    <BarChart3 className="h-8 w-8" />
                  </div>
                  <p className="text-sm font-medium">
                    {language === 'nl' ? 'Impact op omzet' : 'Omzet Impact'}
                  </p>
                  <p className="text-xs text-muted-foreground">
                    {riskLevel === 'excellent' ? '25-40%' : riskLevel === 'moderate' ? '15-25%' : '5-15%'} 
                    {language === 'nl' ? ' verbetering mogelijk' : ' improvement possible'}
                  </p>
                </div>
                
                <div className="text-center">
                  <div className="w-20 h-20 mx-auto rounded-full bg-venture-500/20 text-venture-600 flex items-center justify-center mb-3">
                    <Zap className="h-8 w-8" />
                  </div>
                  <p className="text-sm font-medium">
                    {language === 'nl' ? 'AI Readiness' : 'AI Readiness'}
                  </p>
                  <p className="text-xs text-muted-foreground">
                    {riskLevel === 'excellent' ? 'Hoog' : riskLevel === 'moderate' ? 'Gemiddeld' : 'Laag'}
                  </p>
                </div>
              </div>
              
              {/* Progress Bar Visualization */}
              <div className="space-y-4">
                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span>{language === 'nl' ? 'Attribution Zichtbaarheid' : 'Attribution Visibility'}</span>
                    <span>{Math.max(20, 100 - (answers.attribution || 1) * 20)}%</span>
                  </div>
                  <div className="w-full bg-muted rounded-full h-2">
                    <div 
                      className="bg-gradient-to-r from-primary to-venture-600 h-2 rounded-full transition-all duration-1000"
                      style={{ width: `${Math.max(20, 100 - (answers.attribution || 1) * 20)}%` }}
                    />
                  </div>
                </div>
                
                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span>{language === 'nl' ? 'Board Vertrouwen' : 'Board Confidence'}</span>
                    <span>{Math.max(20, 100 - (answers.board_confidence || 1) * 20)}%</span>
                  </div>
                  <div className="w-full bg-muted rounded-full h-2">
                    <div 
                      className="bg-gradient-to-r from-venture-500 to-booming-600 h-2 rounded-full transition-all duration-1000"
                      style={{ width: `${Math.max(20, 100 - (answers.board_confidence || 1) * 20)}%` }}
                    />
                  </div>
                </div>
                
                <div>
                  <div className="flex justify-between text-sm mb-2">
                    <span>{language === 'nl' ? 'AI Concurrentie Inzicht' : 'AI Competitive Insight'}</span>
                    <span>{Math.max(20, 100 - (answers.ai_readiness || 1) * 20)}%</span>
                  </div>
                  <div className="w-full bg-muted rounded-full h-2">
                    <div 
                      className="bg-gradient-to-r from-booming-500 to-primary h-2 rounded-full transition-all duration-1000"
                      style={{ width: `${Math.max(20, 100 - (answers.ai_readiness || 1) * 20)}%` }}
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* Key Insights */}
            <div className="grid md:grid-cols-2 gap-6">
              <div className="bg-primary/5 border border-primary/20 rounded-xl p-6">
                <h4 className="font-bold text-lg mb-4 text-primary flex items-center gap-2">
                  <TrendingDown className="h-5 w-5" />
                  {language === 'nl' ? 'Grootste Risico\'s' : 'Biggest Risks'}
                </h4>
                <ul className="space-y-3 text-sm">
                  {riskLevel === 'critical' ? (
                    <>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-destructive rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Onvoldoende marketing attributie zichtbaarheid' : 'Insufficient marketing attribution visibility'}
                      </li>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-destructive rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Board heeft beperkt vertrouwen in marketing ROI' : 'Board has limited confidence in marketing ROI'}
                      </li>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-destructive rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Concurrent heeft AI-voorsprong' : 'Competitor has AI advantage'}
                      </li>
                    </>
                  ) : riskLevel === 'moderate' ? (
                    <>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-yellow-500 rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Gedeeltelijke marketing blindspots' : 'Partial marketing blind spots'}
                      </li>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-yellow-500 rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Board vraagt meer verantwoording' : 'Board asks for more accountability'}
                      </li>
                    </>
                  ) : (
                    <>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Sterke systemen, optimalisatie mogelijk' : 'Strong systems, optimization possible'}
                      </li>
                      <li className="flex items-start gap-2">
                        <div className="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0" />
                        {language === 'nl' ? 'Board vertrouwen aanwezig' : 'Board confidence present'}
                      </li>
                    </>
                  )}
                </ul>
              </div>
              
              <div className="bg-venture-500/5 border border-venture-500/20 rounded-xl p-6">
                <h4 className="font-bold text-lg mb-4 text-venture-600 flex items-center gap-2">
                  <Target className="h-5 w-5" />
                  {language === 'nl' ? 'Snelle Wins' : 'Quick Wins'}
                </h4>
                <ul className="space-y-3 text-sm">
                  <li className="flex items-start gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-600 mt-1 flex-shrink-0" />
                    {language === 'nl' ? 'Real-time omzet dashboards implementeren' : 'Implement real-time pipeline dashboards'}
                  </li>
                  <li className="flex items-start gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-600 mt-1 flex-shrink-0" />
                    {language === 'nl' ? 'AI content workflows opstarten (300% sneller)' : 'Start AI content workflows (300% faster)'}
                  </li>
                  <li className="flex items-start gap-2">
                    <CheckCircle className="h-4 w-4 text-venture-600 mt-1 flex-shrink-0" />
                    {language === 'nl' ? 'Board-ready rapportage structuur' : 'Board-ready reporting structure'}
                  </li>
                </ul>
              </div>
            </div>

            {/* Bottom Summary */}
            <div className="text-center bg-gradient-to-r from-primary/5 to-venture-500/5 border border-primary/20 rounded-xl p-6">
              <p className="text-lg mb-4">{result.description}</p>
              <div className="flex flex-wrap justify-center gap-2 text-xs text-muted-foreground">
                <Badge variant="outline" className="px-3 py-1">
                  <Shield className="h-3 w-3 mr-1" />
                  {language === 'nl' ? 'Vertrouwelijk' : 'Confidential'}
                </Badge>
                <Badge variant="outline" className="px-3 py-1">
                  <BarChart3 className="h-3 w-3 mr-1" />
                  {language === 'nl' ? 'Gepersonaliseerd' : 'Personalized'}
                </Badge>
                <Badge variant="outline" className="px-3 py-1">
                  <Target className="h-3 w-3 mr-1" />
                  {language === 'nl' ? 'Uitvoerbaar' : 'Actionable'}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>
      </motion.div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto">
      {/* Urgency Timer */}
      <motion.div 
        animate={{ scale: [1, 1.02, 1] }}
        transition={{ repeat: Infinity, duration: 3 }}
        className="bg-destructive/10 border border-destructive/30 rounded-lg p-3 mb-6 text-center"
      >
        <p className="text-sm font-medium text-destructive">
          {currentContent.urgencyText} <span className="font-mono text-lg">{formatTime(timeLeft)}</span>
        </p>
      </motion.div>

      <Card className="shadow-xl border-2 border-primary/20">
        <CardHeader className="text-center">
          <CardTitle className="text-2xl mb-2">{currentContent.title}</CardTitle>
          <p className="text-muted-foreground">{currentContent.subtitle}</p>
          <div className="mt-4">
            <Progress value={progress} className="h-2" />
            <p className="text-xs text-muted-foreground mt-2">
              {currentStep + 1} / {totalSteps}
            </p>
          </div>
        </CardHeader>

        <CardContent>
          <AnimatePresence mode="wait">
            <motion.div
              key={currentStep}
              initial={{ opacity: 0, x: 20 }}
              animate={{ opacity: 1, x: 0 }}
              exit={{ opacity: 0, x: -20 }}
              className="space-y-4"
            >
              <h3 className="text-lg font-semibold mb-4 text-center">
                {questions[currentStep].question}
              </h3>
              
              <div className="space-y-3">
                {questions[currentStep].options.map((option, index) => (
                   <Button
                     key={index}
                     variant="outline"
                     className="w-full justify-start text-left p-4 h-auto hover:border-primary hover:bg-primary/5 hover:text-foreground text-foreground"
                     onClick={() => handleAnswer(questions[currentStep].id, option.value)}
                   >
                    <span className="bg-primary/10 rounded-full px-3 py-1 text-sm mr-3">
                      {index + 1}
                    </span>
                    {option.label}
                  </Button>
                ))}
              </div>
            </motion.div>
          </AnimatePresence>
        </CardContent>
      </Card>
    </div>
  );
};

export default BoardroomAssessment;