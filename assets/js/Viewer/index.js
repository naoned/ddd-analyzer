import React from 'react'
import ReactDOM from 'react-dom'
import './index.css'
//FIX ME : remove report.json and make webservice call to get report content 
import report from './report.json'
import { options } from './configuration/graph'
import { Viewer } from './Components/Viewer.jsx'

ReactDOM.render(<Viewer options={options} report={report} /> , document.getElementById('viewer'))
