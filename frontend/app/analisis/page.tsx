"use client"

import { useEffect, useState } from "react"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import {
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
} from "recharts"
import { Brain, TrendingUp, TrendingDown, AlertCircle, Lightbulb } from "lucide-react"

export default function AnalisisPage() {
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Simulate loading
    setTimeout(() => setLoading(false), 1000)
  }, [])

  // Mock AI analysis data
  const marketTrends = [
    { month: "Ene", bovino: 45000, porcino: 35000, ovino: 25000, caprino: 20000 },
    { month: "Feb", bovino: 47000, porcino: 36000, ovino: 26000, caprino: 21000 },
    { month: "Mar", bovino: 46500, porcino: 35500, ovino: 25500, caprino: 20500 },
    { month: "Abr", bovino: 48000, porcino: 37000, ovino: 27000, caprino: 22000 },
    { month: "May", bovino: 49500, porcino: 38000, ovino: 28000, caprino: 23000 },
    { month: "Jun", bovino: 51000, porcino: 39000, ovino: 29000, caprino: 24000 },
  ]

  const priceDistribution = [
    { name: "Bovino", value: 45, color: "#16a34a" },
    { name: "Porcino", value: 30, color: "#2563eb" },
    { name: "Ovino", value: 15, color: "#dc2626" },
    { name: "Caprino", value: 10, color: "#ca8a04" },
  ]

  const aiRecommendations = [
    {
      type: "opportunity",
      title: "Oportunidad de Venta - Bovinos",
      description:
        "Los precios del ganado bovino están 12% por encima del promedio histórico. Recomendamos vender lotes de más de 24 meses en las próximas 2 semanas.",
      confidence: 85,
      impact: "Alto",
    },
    {
      type: "warning",
      title: "Alerta de Mercado - Porcinos",
      description:
        "Se detecta una tendencia bajista en el mercado porcino. Considera mantener el stock por 30-45 días adicionales.",
      confidence: 72,
      impact: "Medio",
    },
    {
      type: "insight",
      title: "Análisis Estacional",
      description:
        "Históricamente, los precios aumentan 8-15% durante el próximo trimestre debido a la demanda estacional.",
      confidence: 91,
      impact: "Alto",
    },
  ]

  if (loading) {
    return (
      <div className="p-6">
        <div className="animate-pulse space-y-6">
          <div className="h-8 bg-gray-200 rounded w-1/4"></div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="h-64 bg-gray-200 rounded"></div>
            ))}
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center space-x-3">
        <Brain className="h-8 w-8 text-purple-600" />
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Análisis con IA</h1>
          <p className="text-gray-600">Insights inteligentes para optimizar tus ventas</p>
        </div>
      </div>

      {/* AI Recommendations */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {aiRecommendations.map((rec, index) => (
          <Card key={index} className="border-l-4 border-l-purple-500">
            <CardHeader>
              <div className="flex items-start justify-between">
                <div className="flex items-center space-x-2">
                  {rec.type === "opportunity" && <TrendingUp className="h-5 w-5 text-green-600" />}
                  {rec.type === "warning" && <AlertCircle className="h-5 w-5 text-yellow-600" />}
                  {rec.type === "insight" && <Lightbulb className="h-5 w-5 text-blue-600" />}
                  <CardTitle className="text-lg">{rec.title}</CardTitle>
                </div>
                <Badge variant={rec.impact === "Alto" ? "default" : "secondary"}>{rec.impact}</Badge>
              </div>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-gray-600 mb-4">{rec.description}</p>
              <div className="flex items-center justify-between">
                <span className="text-xs text-gray-500">Confianza: {rec.confidence}%</span>
                <div className="w-20 bg-gray-200 rounded-full h-2">
                  <div className="bg-purple-600 h-2 rounded-full" style={{ width: `${rec.confidence}%` }}></div>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>

      {/* Market Analysis Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card>
          <CardHeader>
            <CardTitle>Tendencias de Precios por Tipo</CardTitle>
            <CardDescription>Evolución de precios últimos 6 meses</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={marketTrends}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip formatter={(value) => [`$${value.toLocaleString()}`, ""]} />
                <Line type="monotone" dataKey="bovino" stroke="#16a34a" strokeWidth={2} name="Bovino" />
                <Line type="monotone" dataKey="porcino" stroke="#2563eb" strokeWidth={2} name="Porcino" />
                <Line type="monotone" dataKey="ovino" stroke="#dc2626" strokeWidth={2} name="Ovino" />
                <Line type="monotone" dataKey="caprino" stroke="#ca8a04" strokeWidth={2} name="Caprino" />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Distribución del Mercado</CardTitle>
            <CardDescription>Participación por tipo de animal</CardDescription>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={priceDistribution}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {priceDistribution.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Detailed Analysis */}
      <Card>
        <CardHeader>
          <CardTitle>Análisis Detallado del Mercado</CardTitle>
          <CardDescription>Insights generados por IA basados en datos históricos y tendencias actuales</CardDescription>
        </CardHeader>
        <CardContent className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-gray-900">Factores de Mercado</h3>
              <div className="space-y-3">
                <div className="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                  <span className="text-sm font-medium text-green-800">Demanda Estacional</span>
                  <Badge className="bg-green-100 text-green-800">+15%</Badge>
                </div>
                <div className="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                  <span className="text-sm font-medium text-blue-800">Exportaciones</span>
                  <Badge className="bg-blue-100 text-blue-800">+8%</Badge>
                </div>
                <div className="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                  <span className="text-sm font-medium text-yellow-800">Costos de Alimentación</span>
                  <Badge className="bg-yellow-100 text-yellow-800">-3%</Badge>
                </div>
              </div>
            </div>

            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-gray-900">Predicciones IA</h3>
              <div className="space-y-3">
                <div className="p-4 border rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <span className="font-medium">Próximos 30 días</span>
                    <TrendingUp className="h-4 w-4 text-green-600" />
                  </div>
                  <p className="text-sm text-gray-600">Aumento proyectado del 5-8% en precios de bovinos</p>
                </div>
                <div className="p-4 border rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <span className="font-medium">Próximos 90 días</span>
                    <TrendingDown className="h-4 w-4 text-red-600" />
                  </div>
                  <p className="text-sm text-gray-600">Posible corrección del mercado, estabilización de precios</p>
                </div>
              </div>
            </div>
          </div>

          <div className="border-t pt-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Recomendaciones Personalizadas</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div className="p-4 bg-purple-50 rounded-lg border border-purple-200">
                <h4 className="font-medium text-purple-800 mb-2">Estrategia de Venta</h4>
                <p className="text-sm text-purple-700">
                  Vende el 60% de tu stock bovino en las próximas 3 semanas para maximizar ganancias.
                </p>
              </div>
              <div className="p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                <h4 className="font-medium text-indigo-800 mb-2">Diversificación</h4>
                <p className="text-sm text-indigo-700">
                  Considera aumentar tu stock de ovinos, el mercado muestra signos de crecimiento.
                </p>
              </div>
              <div className="p-4 bg-emerald-50 rounded-lg border border-emerald-200">
                <h4 className="font-medium text-emerald-800 mb-2">Timing Óptimo</h4>
                <p className="text-sm text-emerald-700">
                  El mejor momento para vender será entre el 15-25 del próximo mes.
                </p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}
