import React, { Component } from 'react'
import Graph from 'react-graph-vis'
import PropTypes from 'prop-types'
import { GraphConverter } from '../Converters/Graph.js'

export class Viewer extends Component
{
    static propTypes = {
        options: PropTypes.object.isRequired,
        report: PropTypes.object.isRequired,
    }
    
    render() {
        const { options, report } = this.props
        
        const converter = new GraphConverter()
        
        return (
            <Graph graph={converter.convertFromJson(report)} options={options} />
        )
    }
}
