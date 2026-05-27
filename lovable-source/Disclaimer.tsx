
import { motion } from "framer-motion";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

const Disclaimer = () => {
  return (
    <div className="min-h-screen flex flex-col">
      <Navbar />
      
      <main className="flex-grow bg-gradient-to-br from-white via-booming-50/30 to-venture-50/30">
        <div className="container mx-auto px-4 md:px-6 py-16">
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6 }}
            className="max-w-4xl mx-auto"
          >
            <h1 className="text-4xl md:text-5xl font-bold gradient-text mb-8">
              Disclaimer
            </h1>
            
            <div className="bg-white rounded-xl shadow-lg p-8 md:p-12 space-y-8">
              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  General Information
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  The information provided on this website is for general informational purposes only. 
                  While Booming Venture strives to keep the information up to date and correct, we make 
                  no representations or warranties of any kind, express or implied, about the completeness, 
                  accuracy, reliability, suitability or availability with respect to the website or the 
                  information, products, services, or related graphics contained on the website for any purpose.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Marketing and Business Results
                </h2>
                <p className="text-gray-700 leading-relaxed mb-4">
                  Any marketing performance data, ROI projections, or business growth examples shared 
                  on this website are based on specific circumstances and should not be considered 
                  typical results. Individual results may vary significantly based on various factors 
                  including but not limited to:
                </p>
                <ul className="list-disc pl-6 text-gray-700 space-y-2">
                  <li>Market conditions and industry dynamics</li>
                  <li>Business model and target audience</li>
                  <li>Marketing budget and resource allocation</li>
                  <li>Implementation quality and consistency</li>
                  <li>Competitive landscape and timing</li>
                </ul>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  AI and Technology Solutions
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  Our AI-powered marketing solutions and automated tools are designed to enhance 
                  marketing performance, but their effectiveness depends on proper implementation, 
                  data quality, and ongoing optimization. AI technology is rapidly evolving, and 
                  performance may vary based on algorithm updates, platform changes, and data 
                  availability. We do not guarantee specific outcomes from AI implementations.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Professional Advice
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  The content on this website does not constitute professional advice. Before making 
                  any business decisions based on the information provided, we recommend consulting 
                  with qualified professionals in relevant fields including marketing, legal, 
                  financial, and technical experts who can provide advice tailored to your specific 
                  situation and requirements.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Third-Party Links and Services
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  Our website may contain links to third-party websites, tools, or services. These 
                  links are provided for convenience only, and we do not endorse or take responsibility 
                  for the content, privacy policies, or practices of these external sites. Users 
                  access third-party content at their own risk.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Limitation of Liability
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  In no event will Booming Venture be liable for any loss or damage including, without 
                  limitation, indirect or consequential loss or damage, or any loss or damage whatsoever 
                  arising from loss of data or profits arising out of, or in connection with, the use 
                  of this website or our services.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Data and Privacy
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  While we implement industry-standard security measures to protect user data, we 
                  cannot guarantee absolute security. Users are responsible for maintaining the 
                  confidentiality of their account information and for all activities that occur 
                  under their account. Please refer to our Privacy Policy for detailed information 
                  about data collection and processing.
                </p>
              </section>

              <section>
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Updates and Modifications
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  This disclaimer may be updated from time to time without prior notice. We encourage 
                  users to review this page periodically for any changes. Continued use of our website 
                  and services after modifications constitutes acceptance of the updated disclaimer.
                </p>
              </section>

              <section className="border-t pt-8">
                <h2 className="text-2xl font-semibold text-gray-900 mb-4">
                  Contact Information
                </h2>
                <p className="text-gray-700 leading-relaxed">
                  If you have any questions about this disclaimer or our services, please contact us at:
                </p>
                <div className="mt-4 text-gray-700">
                  <p>Email: info@boomingventure.com</p>
                  <p>Address: Breedveldsingel 1, 3055 PG Rotterdam, The Netherlands</p>
                </div>
              </section>

              <div className="text-sm text-gray-500 border-t pt-6">
                <p>Last updated: {new Date().toLocaleDateString('en-US', { 
                  year: 'numeric', 
                  month: 'long', 
                  day: 'numeric' 
                })}</p>
              </div>
            </div>
          </motion.div>
        </div>
      </main>
      
      <Footer />
    </div>
  );
};

export default Disclaimer;
